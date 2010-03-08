/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 * 
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= 
 */

using System;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;
using com.xmlnuke.engine;
using com.xmlnuke.anydataset;
using com.xmlnuke.international;
using com.xmlnuke.util;
using com.xmlnuke.processor;

namespace com.xmlnuke.classes
{

    /// <summary>
    /// Define what XmlInputObject the system will render on editing mode. 
    /// </summary>
    public enum XmlInputObjectType
    {
        TEXTBOX,
        PASSWORD,
        CHECKBOX,
        RADIOBUTTON,
        MEMO,
        HIDDEN,
        FILE,
        SELECTLIST,
        DUALLIST,
        HTMLTEXT,
        TEXTBOX_AUTOCOMPLETE,
        DATE,
        DATETIME,
        FILEUPLOAD,
        CUSTOM        // This fields must be validate by user
    }

    /// <summary>
    /// Define the data dictionary about the informations will be managed. Contains important informations about visibibility in list, editing object type, data type, if the field is a key field or if is editable, and more.
    /// A array of ProcessPageField must be defined for the ProcessPageStateBase class. 
    /// </summary>
    public class ProcessPageField
    {
        public string fieldName;
        public string fieldCaption;
        public XmlInputObjectType fieldXmlInput;
        public INPUTTYPE dataType;
        public int size;
        public int maxLength;
        public string rangeMin;
        public string rangeMax;
        public bool visibleInList;
        public bool editable;
        public bool required;
        public bool key;
        public string defaultValue;
        public bool newColumn;
        public IEditListFormatter editListFormatter;
        public IEditListFormatter editFormatter;
        public IEditListFormatter saveDatabaseFormatter;
        public NameValueCollection arraySelectList;

        public ProcessPageField(bool newcolumn)
        {
            fieldName = "";
            fieldCaption = "";
            fieldXmlInput = XmlInputObjectType.TEXTBOX;
            dataType = INPUTTYPE.TEXT;
            size = 20;
            maxLength = 0;
            rangeMin = "";
            rangeMax = "";
            visibleInList = true;
            editable = true;
            required = true;
            key = false;
            defaultValue = "";
            newColumn = newcolumn;
            editListFormatter = null;
            arraySelectList = new NameValueCollection();
        }

        public ProcessPageField()
            : this(true)
        { }
    }

    public class ProcessPageFields
    {
        protected ArrayList fields;

        public ProcessPageFields()
        {
            this.fields = new ArrayList();
        }

        public void addProcessPageField(ProcessPageField p)
        {
            this.fields.Add(p);
        }

        public ProcessPageField[] getProcessPageFields()
        {
            return (ProcessPageField[])this.fields.ToArray(typeof(ProcessPageField));
        }

        /// <summary>
        /// Factory to create ProcessPageField Objects
        /// </summary>
        /// <param name="name"></param>
        /// <param name="caption"></param>
        /// <param name="dataType"></param>
        /// <param name="XmlxmlObject"></param>
        /// <param name="size"></param>
        /// <param name="maxLength"></param>
        /// <param name="visible"></param>
        /// <param name="required"></param>
        /// <returns></returns>
        public static ProcessPageField Factory(string name, string caption, INPUTTYPE dataType, XmlInputObjectType xmlObject, int size, int maxLength, bool visible, bool required)
        {
            ProcessPageField fieldPage = new ProcessPageField();
            fieldPage.fieldName = name;
            fieldPage.fieldCaption = caption;
            fieldPage.key = false;
            fieldPage.dataType = dataType;
            fieldPage.size = size;
            fieldPage.maxLength = maxLength;
            fieldPage.fieldXmlInput = xmlObject;
            fieldPage.visibleInList = visible;
            fieldPage.editable = true;
            fieldPage.required = required;
            return fieldPage;
        }

        public static ProcessPageField Factory(string name, string caption, int maxLength, bool visible, bool required)
        {
            return ProcessPageFields.Factory(name, caption, INPUTTYPE.TEXT, XmlInputObjectType.TEXTBOX, maxLength, maxLength, visible, required);
        }
    }


    /// <summary>
    /// Abstract class it provides all necessary procedures to VIEW, EDIT, INSERT and DELETE
    /// data from a AnyDataSet document, BDDataSet or another defined by user.
    /// Using this class the user can abstract the page state control, passing parameters, retrieve/store data from/to the repository, and other details.
    /// See also: <see cref="T:com.xmlnuke.classes.ProcessPageStateDB"/> and <see cref="T:com.xmlnuke.classes.ProcessPageStateDataSet"/> for examples.
    /// </summary>
    /// <remarks>The user *must* inherit this class and implement at least three functions: 
    /// getAllRecords(), getCurrentRecord() and updateRecord(); The classes ProcessPageStateDataSet and ProcessPageStareDB implements this methods for basic input/output data in AnyDataSet and single tables in relational databases.
    /// </remarks>
    public abstract class ProcessPageStateBase : XmlnukeDocumentObject, IProcessPageState
    {
        public static string ACTION_LIST = "";
        public static string ACTION_NEW = "ppnew";
        public static string ACTION_EDIT = "ppedit";
        public static string ACTION_VIEW = "ppview";
        public static string ACTION_DELETE = "ppdelete";
        public static string ACTION_NEW_CONFIRM = "ppnew_confirm";
        public static string ACTION_EDIT_CONFIRM = "ppedit_confirm";
        public static string ACTION_DELETE_CONFIRM = "ppdelete_confirm";
        public static string ACTION_MSG = "ppmsgs";
        public static string PARAM_MSG = "ppmsgtext";
        public static string PARAM_CANCEL = "ppbtncancel";

        protected string _currentAction;
        protected string _nextAction;
        protected string _header;
        protected string _module;
        protected ArrayList _keyIndex = new ArrayList();
        protected Context _context;
        protected ProcessPageField[] _fields;
        protected CustomButtons[] _buttons;
        protected string _filter;
        protected string _sort;
        protected string _valueId;

        protected int _curPage;
        protected int _qtdRows;

        protected bool _new;
        protected bool _view;
        protected bool _edit;
        protected bool _delete;

        protected char _decimalSeparator;
        protected DATEFORMAT _dateFormat;

        protected LanguageCollection _lang;

        protected NameValueCollection _parameter;

        /// <summary>
        /// Constructor.
        /// </summary>
        /// <param name="context">XMLNuke context class</param>
        /// <param name="fields">ProcessPageField. Contains the metadata definition for the fields will be retrive, stored and processed.</param>
        /// <param name="header">Simple string header</param>
        /// <param name="module">Module will be process the request. This is the module it contains ProcessPage definitions.</param>
        /// <param name="buttons">Custom buttons used in View/Select mode</param>
        public ProcessPageStateBase(Context context, ProcessPageFields fields, string header, string module, CustomButtons[] buttons)
            : base()
        {
            this._context = context;
            this._fields = fields.getProcessPageFields();
            this._buttons = buttons;
            this._header = header;
            this._module = module;

            this._new = true;
            this._view = true;
            this._delete = true;
            this._edit = true;

            this._currentAction = this._context.ContextValue("acao");

            for (int i = 0; i < this._fields.Length; i++)
            {
                if (this._fields[i].key)
                {
                    this._keyIndex.Add(i);
                }
            }

            //this._filter = this._context.ContextValue("filter");
            //this._sort = this._context.ContextValue("sort");
            this._valueId = this._context.ContextValue("valueid");

            try
            {
                this._curPage = Convert.ToInt32(this._context.ContextValue("curpage"));
            }
            catch
            {
                this._curPage = 0;
            }
            try
            {
                this._qtdRows = Convert.ToInt32(this._context.ContextValue("offset"));
            }
            catch
            {
                this._qtdRows = 0;
            }

            this._parameter = new NameValueCollection();

            System.Globalization.CultureInfo currentCulture = this._context.Language;
            this._decimalSeparator = currentCulture.NumberFormat.NumberDecimalSeparator[0];
            this._dateFormat = DATEFORMAT.DMY;
            this._lang = LanguageFactory.GetLanguageCollection(context, LanguageFileTypes.OBJECT, "com.xmlnuke.classes.ProcessPageStateBase");
        }

        /// <summary>
        /// Use this feature to enable list the records in pages. The number of rows per page is defined in qtdRows attribute.
        /// </summary>
        /// <remarks>
        /// Note: This feature only is a wrap for the EditList.setPageSize.
        /// </remarks>
        /// <param name="qtdRows">Number of rows visible.</param>
        /// <param name="curPage">Number of the current page. If you set this value to ZERO (0) the system will manage the current page.</param>
        public void setPageSize(int qtdRows, int curPage)
        {
            if (curPage != 0)
            {
                this._curPage = curPage;
            }
            this._qtdRows = qtdRows;
        }

        /// <summary>
        /// Define what functions will be enable in ProcessPageStateBase. Available options are: enable/disable insert records, enable/disable view, enable/disable edit records, enable/disable delete records.
        /// </summary>
        /// <param name="newRec">True enable insert records; False, disable.</param>
        /// <param name="view">True enable view detailed informations; False, disable.</param>
        /// <param name="edit">True enable edit records; False, disable.</param>
        /// <param name="delete">True enable delete records; False, disable.</param>
        public void setPermissions(bool newRec, bool view, bool edit, bool delete)
        {
            this._new = newRec;
            this._view = view;
            this._delete = delete;
            this._edit = edit;
        }

        /// <summary>
        /// Returns all valid records. This method is called internally for listing purposes (read only) and must return only the fields set with visibleInList=true. 
        /// </summary>
        /// <returns>IIterator</returns>
        public abstract IIterator getAllRecords();

        /// <summary>
        /// Returns the current record. This method is called internally in edit mode (view, delete or update).
        /// </summary>
        /// <remarks>
        /// The current record is filtered by the fieldPage with the parameter key is set to TRUE. Developers can use  _fields[_keyIndex] to get the key field (or value).
        /// </remarks>
        /// <returns>SingleRow</returns>
        public abstract SingleRow getCurrentRecord();

        /// <summary>
        /// Define all necessaries commands for insert, update or delete a record.
        /// </summary>
        /// <remarks>
        /// Developers must test what is the current action to execute the proper command. 
        /// <code>
        /// if (this._currentAction == ACTION_NEW_CONFIRM)
        /// {
        /// 	// command for insert a field
        /// }
        /// else if (this._currentAction == ACTION_EDIT_CONFIRM)
        /// {
        /// 	// command for insert a field
        /// }
        /// else if (this._currentAction == ACTION_DELETE_CONFIRM)
        /// {
        /// 	// command for insert a field
        /// }
        /// </code>
        /// </remarks>
        /// <returns>Return a IXmlnukeDocumentObject. This object must inform the user if the action is successfully performed or not.
        /// <code>
        /// return new XmlnukeText("The record was added", true, true, false);
        /// </code>
        /// </returns>
        public abstract IXmlnukeDocumentObject updateRecord();

        /// <summary>
        /// Defines a filter of the records. The descendant must implement how the class will filter records using this option.
        /// </summary>
        /// <param name="filter">String filter.</param>
        public void setFilter(string filter)
        {
            this._filter = filter; // Convert.ToBase64String(System.Text.Encoding.UTF8.GetBytes(filter));
        }

        /// <summary>
        /// Retrieves de current filter.
        /// </summary>
        /// <param name="filter"></param>
        public string getFilter()
        {
            return this._filter; // System.Text.Encoding.UTF8.GetString(Convert.FromBase64String(this._filter));
        }

        /// <summary>
        /// Defines a string for sorting the records. The descendant must implement how the class will sort records using this option.
        /// </summary>
        /// <param name="sort">String sort.</param>
        public void setSort(string sort)
        {
            this._sort = sort; // Convert.ToBase64String(System.Text.Encoding.UTF8.GetBytes(sort));
        }

        /// <summary>
        /// Retrieve the current sort.
        /// </summary>
        /// <returns></returns>
        public string getSort()
        {
            return this._sort; // System.Text.Encoding.UTF8.GetString(Convert.FromBase64String(this._sort));
        }

        public void setFormParameters(char decimalSeparator, DATEFORMAT dateFormat)
        {
            this._decimalSeparator = decimalSeparator;
            this._dateFormat = dateFormat;
        }

        /// <summary>
        /// Adds extra information to process page. This information will be persisted during the requests.
        /// </summary>
        /// <param name="name"></param>
        /// <param name="value"></param>
        public void addParameter(string name, string value)
        {
            this._parameter.Add(name, value);
        }

        /// <summary>
        /// ProcessPageStateBase know what is the current action. The user may force the new current action. See: forceCurrentValueId().
        /// </summary>
        /// <remarks>
        /// This option is intended to be used for advanced users because the results are unexpected if some parameter is wrong.
        /// </remarks>
        /// <param name="action">The current action. Valid options are: ACTION_NEW, ACTION_NEW_CONFIRM, ACTION_VIEW, ACTION_EDIT, ACTION_EDIT_CONFIRM, ACTION_DELETE, ACTION_DELETE_CONFIRM.</param>
        public void forceCurrentAction(string action)
        {
            this._currentAction = action;
        }

        /// <summary>
        /// Set the new current valueid. See: forceCurrentAction()
        /// </summary>
        /// <remarks>
        /// This method is used in conjunction with forceCurrentAction. For example:
        /// <code>
        /// processPage.forceCurrentAction = ProcessStatePageBase.ACTION_EDIT;
        /// processPage.forceCurrentValueId = "10";
        /// </code>
        /// The option above will be edit the record where the key have the value equal to 10.
        /// </remarks>
        /// <param name="valueId"></param>
        public void forceCurrentValueId(string valueId)
        {
            this._valueId = valueId;
        }

        /// <summary>
        /// Validate the input data before update or insert at server side. 
        /// </summary>
        /// <remarks>
        /// ProcessPageStateBase uses the XMLNuke engine to validate the data at client side (using JavaScript) and at server side using this method. 
        /// This method may be override to implment custom business rules.
        /// </remarks>
        /// <returns>Return an IXmlnukeDocumentObject if the validate fails. Null if validate is ok.</returns>
        public virtual IXmlnukeDocumentObject validateUpdate()
        {
            if ((this._currentAction != ACTION_EDIT_CONFIRM) && (this._currentAction != ACTION_NEW_CONFIRM))
            {
                return null;
            }

            NameValueCollection nvc = new NameValueCollection();

            for (int i = 0; i < this._fields.Length; i++)
            {
                ProcessPageField field = this._fields[i];
                string curValue = this._context.ContextValue(this._fields[i].fieldName);

                if (field.editable)
                {
                    if (this._fields[i].fieldXmlInput == XmlInputObjectType.FILEUPLOAD)
                    {
                        continue;
                    }
                    else if ((curValue == "") && (field.required))
                    {
                        nvc.Add("err" + i.ToString(), this._lang.Value("ERR_REQUIRED", field.fieldCaption));
                    }
                    else if (this._fields[i].dataType == INPUTTYPE.NUMBER)
                    {
                        double result = new double();
                        System.Globalization.NumberFormatInfo provider = new System.Globalization.NumberFormatInfo();
                        provider.NumberDecimalSeparator = this._decimalSeparator.ToString();
                        provider.CurrencyDecimalSeparator = this._decimalSeparator.ToString();
                        if (!Double.TryParse(curValue, System.Globalization.NumberStyles.Any, provider, out result))
                        {
                            nvc.Add("err" + i.ToString(), this._lang.Value("ERR_INVALIDNUMBER", field.fieldCaption));
                        }
                    }
                }
            }

            if (nvc.Count != 0)
            {
                XmlParagraphCollection p = new XmlParagraphCollection();
                p.addXmlnukeObject(new XmlEasyList(EasyListType.UNORDEREDLIST, "Error", this._lang.Value("ERR_FOUND"), nvc, ""));
                XmlAnchorCollection a = new XmlAnchorCollection("javascript:history.go(-1)", "");
                a.addXmlnukeObject(new XmlnukeText(this._lang.Value("TXT_BACK")));
                p.addXmlnukeObject(a);
                return p;
            }
            else
            {
                return null;
            }
        }

        /// <summary>
        /// This function defines how the records will be listing. 
        /// </summary>
        /// <remarks>
        /// The default list behavior is using the XmlEditList object, but the user can override this method to implement your own behavior.
        /// </remarks>
        /// <returns>Return a IXmlnukeDocumentObject it contains all records.</returns>
        protected virtual IXmlnukeDocumentObject listAllRecords()
        {
            XmlEditList editList = new XmlEditList(this._context, this._header, this._module, false, false, false, false);
            editList.setDataSource(this.getAllRecords());
            editList.setPageSize(this._qtdRows, this._curPage);
            editList.setEnablePage(true);
            //editList.addParameter("filter", this._filter);
            //editList.addParameter("sort", this._sort);
            foreach (string name in this._parameter.Keys)
            {
                editList.addParameter(name, this._parameter[name]);
            }

            CustomButtons cb = new CustomButtons();
            if (this._new)
            {
                cb = new CustomButtons();
                cb.action = ACTION_NEW;
                cb.alternateText = this._lang.Value("TXT_NEW");
                cb.icon = "common/editlist/ic_novo.gif";
                cb.enabled = true;
                cb.multiple = MultipleSelectType.NONE;
                editList.setCustomButton(cb);
            }
            if (this._view)
            {
                cb = new CustomButtons();
                cb.action = ACTION_VIEW;
                cb.alternateText = this._lang.Value("TXT_VIEW");
                cb.icon = "common/editlist/ic_detalhes.gif";
                cb.enabled = true;
                cb.multiple = MultipleSelectType.ONLYONE;
                editList.setCustomButton(cb);
            }
            if (this._edit)
            {
                cb = new CustomButtons();
                cb.action = ACTION_EDIT;
                cb.alternateText = this._lang.Value("TXT_EDIT");
                cb.icon = "common/editlist/ic_editar.gif";
                cb.enabled = true;
                cb.multiple = MultipleSelectType.ONLYONE;
                editList.setCustomButton(cb);
            }
            if (this._delete)
            {
                cb = new CustomButtons();
                cb.action = ACTION_DELETE;
                cb.alternateText = this._lang.Value("TXT_DELETE");
                cb.icon = "common/editlist/ic_excluir.gif";
                cb.enabled = true;
                cb.multiple = MultipleSelectType.ONLYONE;
                editList.setCustomButton(cb);
            }


            if (this._buttons != null)
            {
                for (int i = 0; i < this._buttons.Length; i++)
                {
                    cb = editList.getCustomButton();
                    cb.action = this._buttons[i].action;
                    cb.alternateText = this._buttons[i].alternateText;
                    cb.icon = this._buttons[i].icon;
                    cb.url = this._buttons[i].url;
                    cb.enabled = true;
                    cb.multiple = this._buttons[i].multiple;
                    editList.setCustomButton(cb);
                }
            }

            string fldKey = "";
            for (int i = 0; i < this._keyIndex.Count; i++)
            {
                fldKey += ((fldKey != "") ? "|" : "") + this._fields[((int)this._keyIndex[i])].fieldName;
            }
            EditListField field = new EditListField();
            field.fieldData = fldKey;
            field.editlistName = "#";
            field.formatter = new ProcessPageStateBaseFormatterKey();
            field.fieldType = EditListFieldType.FORMATTER;
            editList.addEditListField(field);

            for (int i = 0; i < this._fields.Length; i++)
            {
                if (this._fields[i].visibleInList)
                {
                    field = new EditListField(true);
                    field.fieldData = this._fields[i].fieldName;
                    field.editlistName = this._fields[i].fieldCaption;
                    if (this._fields[i].fieldXmlInput == XmlInputObjectType.SELECTLIST)
                    {
                        field.fieldType = EditListFieldType.LOOKUP;
                        field.arrayLookup = this._fields[i].arraySelectList;
                    }
                    else if (this._fields[i].fieldXmlInput == XmlInputObjectType.DUALLIST)
                    {
                        field.fieldType = EditListFieldType.FORMATTER;
                        field.formatter = new ProcessPageStateBaseFormatterDualList(this._fields[i].arraySelectList);
                    }
                    else
                    {
                        field.fieldType = EditListFieldType.TEXT;
                    }
                    field.newColumn = this._fields[i].newColumn;
                    if (this._fields[i].editListFormatter != null)
                    {
                        field.formatter = this._fields[i].editListFormatter;
                        field.fieldType = EditListFieldType.FORMATTER;
                    }

                    field = this.editListFieldCustomize(field, this._fields[i]);

                    editList.addEditListField(field);
                }
            }
            return editList;
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="editListField"></param>
        /// <param name="field"></param>
        /// <returns></returns>
        protected virtual EditListField editListFieldCustomize(EditListField editListField, ProcessPageField field)
        {
            return editListField;
        }

        /// <summary>
        /// Internal method. Check if the field is read-only or not. 
        /// </summary>
        /// <param name="field">The field will be checked.</param>
        /// <returns>True if is read-only; False if not.</returns>
        protected bool isReadOnly(ProcessPageField field)
        {
            bool formReadOnly = true;
            if ((this._currentAction == ACTION_EDIT) || (this._currentAction == ACTION_NEW))
            {
                formReadOnly = false;
            }

            bool fieldReadOnly = (!field.editable);

            return (formReadOnly || fieldReadOnly || (field.key && this._currentAction != ACTION_NEW));
        }

        protected virtual IXmlnukeDocumentObject showResultMessage()
        {
            string msg = this._context.ContextValue(PARAM_MSG);
            string message = "";

            if (msg == ACTION_NEW_CONFIRM)
            {
                message = this._lang.Value("MSG_NEW_SUCCESS");
            }
            else if (msg == ACTION_EDIT_CONFIRM)
            {
                message = this._lang.Value("MSG_EDIT_SUCCESS");
            }
            else if (msg == ACTION_DELETE_CONFIRM)
            {
                message = this._lang.Value("MSG_DELETE_SUCCESS");
            }
            else
            {
                message = this._lang.Value("MSG_NOCHANGE");
            }

            XmlnukeUIAlert container = new XmlnukeUIAlert(this._context, UIAlert.BoxInfo);
            container.setAutoHide(8000);
            container.addXmlnukeObject(new XmlnukeText(message, true, true, false));

            return container;
        }

        /// <summary>
        /// This function defines how the record will be edit. 
        /// </summary>
        /// <remarks>
        /// The default edit behavior is using the XmlFormCollection and XmlInput objects. 
        /// This is very interesting because the result produced will be a XML ever and dont need be overrides. If the user want customize your own field (for example a Select List or a Lookup list) is necessary overrides the renderField method.
        /// </remarks>
        /// <returns>XmlFormCollection it contains all field will be edit/insert.</returns>
        protected virtual XmlFormCollection showCurrentRecord()
        {
            string title = "";
            if (this._currentAction == ACTION_NEW)
            {
                title = this._lang.Value("TITLE_NEW", this._header);
            }
            else if (this._currentAction == ACTION_EDIT)
            {
                title = this._lang.Value("TITLE_EDIT", this._header);
            }
            else if (this._currentAction == ACTION_DELETE)
            {
                title = this._lang.Value("TITLE_DELETE", this._header);
            }
            else if (this._currentAction == ACTION_VIEW)
            {
                title = this._lang.Value("TITLE_VIEW", this._header);
            }


            XmlFormCollection form = new XmlFormCollection(this._context, this._module, title);
            form.setDecimalSeparator(this._decimalSeparator);
            form.setDateFormat(this._dateFormat);

            //form.addXmlnukeObject(new XmlInputHidden("filter", this._filter));
            //form.addXmlnukeObject(new XmlInputHidden("sort", this._sort));
            form.addXmlnukeObject(new XmlInputHidden("curpage", this._curPage.ToString()));
            form.addXmlnukeObject(new XmlInputHidden("offset", this._qtdRows.ToString()));
            form.addXmlnukeObject(new XmlInputHidden("acao", this._currentAction + "_confirm"));
            form.addXmlnukeObject(new XmlInputHidden("valueid", this._valueId));
            foreach (string name in this._parameter.Keys)
            {
                form.addXmlnukeObject(new XmlInputHidden(name, this._parameter[name]));
            }

            form.setJSValidate(true);
            form.setDecimalSeparator(this._decimalSeparator);
            form.setDateFormat(this._dateFormat);

            SingleRow sr = this.getCurrentRecord();

            for (int i = 0; i < this._fields.Length; i++)
            {
                string curValue = "";
                if (this._currentAction != ACTION_NEW)
                {
                    curValue = sr.getField(this._fields[i].fieldName);
                    if (((this._fields[i].dataType == INPUTTYPE.DATE) || (this._fields[i].dataType == INPUTTYPE.DATETIME)) && (curValue != ""))
                    {
                        curValue = this.dateFromSource(curValue, (this._fields[i].dataType == INPUTTYPE.DATETIME));
                    }
                    else if (this._fields[i].dataType == INPUTTYPE.NUMBER)
                    {
                        curValue = curValue.Replace('.', this._decimalSeparator);
                    }

                    if (this._fields[i].editFormatter != null)
                    {
                        curValue = this._fields[i].editFormatter.Format(sr, this._fields[i].fieldName, curValue);
                    }
                }
                else
                {
                    curValue = this._fields[i].defaultValue;
                }

                form.addXmlnukeObject(this.renderField(this._fields[i], curValue));
            }

            XmlInputButtons buttons = new XmlInputButtons();
            if (this._currentAction != ACTION_VIEW)
            {
                buttons.addSubmit(this._lang.Value("TXT_SUBMIT"), "");
            }
            buttons.addButton(this._lang.Value("TXT_BACK"), "", "document.location='" + this.redirProcessPage(true) + "'");
            form.addXmlnukeObject(buttons);

            return form;
        }


        protected string dateFromSource(string curValue)
        {
            return this.dateFromSource(curValue, false);
        }

        /// <summary>
        /// Format a date field from Database values
        /// </summary>
        /// <param name="curValue"></param>
        /// <param name="hour"></param>
        /// <returns></returns>
        protected virtual string dateFromSource(string curValue, bool hour)
        {
            try
            {
                return DateUtil.ConvertDate(curValue, DATEFORMAT.YMD, this._dateFormat, hour);
            }
            catch
            {
                return "??/??/????";
            }
        }

        /// <summary>
        /// Define how ProcessPageStateBase will be render a single field.
        /// </summary>
        /// <remarks>
        /// This method must be override if the user wants create custom fields like SelectField or a LookupField.
        /// </remarks>
        /// <param name="field">The field will be renderized.</param>
        /// <param name="curValue">The current value of the field.</param>
        /// <returns>Return a XmlInput Object it contains the field.</returns>
        public virtual IXmlnukeDocumentObject renderField(ProcessPageField field, string curValue)
        {
            if ((field.fieldXmlInput == XmlInputObjectType.TEXTBOX) || (field.fieldXmlInput == XmlInputObjectType.PASSWORD) || (field.fieldXmlInput == XmlInputObjectType.TEXTBOX_AUTOCOMPLETE))
            {
                XmlInputTextBox itb = new XmlInputTextBox(field.fieldCaption, field.fieldName, curValue, field.size);
                itb.setRequired(field.required);
                itb.setRange(field.rangeMin, field.rangeMax);
                itb.setDescription(field.fieldCaption);
                if (field.fieldXmlInput == XmlInputObjectType.PASSWORD)
                {
                    itb.setInputTextBoxType(InputTextBoxType.PASSWORD);
                }
                else if (field.fieldXmlInput == XmlInputObjectType.TEXTBOX_AUTOCOMPLETE)
                {
                    if ((field.arraySelectList != null) || (String.IsNullOrEmpty(field.arraySelectList["URL"])) || (String.IsNullOrEmpty(field.arraySelectList["PARAMREQ"])))
                    {
                        throw new Exception("You have to pass a array to arraySelectList field parameter with the following keys: URL, PARAMREQ");
                    }
                    itb.setInputTextBoxType(InputTextBoxType.TEXT);
                    itb.setAutosuggest(this._context, field.arraySelectList["URL"], field.arraySelectList["PARAMREQ"]);
                }
                else
                {
                    itb.setInputTextBoxType(InputTextBoxType.TEXT);
                }
                itb.setMaxLength(field.maxLength);
                itb.setReadOnly(this.isReadOnly(field));
                itb.setDataType(field.dataType);
                return itb;
            }
            else if ((field.fieldXmlInput == XmlInputObjectType.RADIOBUTTON) || (field.fieldXmlInput == XmlInputObjectType.CHECKBOX))
            {
                XmlInputCheck ic = new XmlInputCheck(field.fieldCaption, field.fieldName, field.defaultValue);
                if (field.fieldXmlInput == XmlInputObjectType.TEXTBOX)
                {
                    ic.setType(InputCheckType.CHECKBOX);
                }
                else
                {
                    ic.setType(InputCheckType.CHECKBOX);
                }
                ic.setChecked(field.defaultValue == curValue);
                ic.setReadOnly(this.isReadOnly(field));
                return ic;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.MEMO)
            {
                XmlInputMemo im = new XmlInputMemo(field.fieldCaption, field.fieldName, curValue);
                im.setWrap("SOFT");
                im.setSize(50, 8);
                im.setReadOnly(this.isReadOnly(field));
                return im;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.HTMLTEXT)
            {
                XmlInputMemo im = new XmlInputMemo(field.fieldCaption, field.fieldName, curValue);
                im.setVisualEditor(true);
                im.setReadOnly(this.isReadOnly(field));
                return im;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.HIDDEN)
            {
                XmlInputHidden ih = new XmlInputHidden(field.fieldName, curValue);
                return ih;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.SELECTLIST)
            {
                XmlEasyList el = new XmlEasyList(EasyListType.SELECTLIST, field.fieldName, field.fieldCaption, field.arraySelectList, curValue);
                el.setReadOnly(this.isReadOnly(field));
                return el;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.DUALLIST)
            {
                ArrayDataSet ards = new ArrayDataSet(field.arraySelectList, "value");
                XmlDualList duallist = new XmlDualList(this._context, field.fieldName, this._lang.Value("TXT_AVAILABLE", field.fieldCaption), this._lang.Value("TXT_USED", field.fieldCaption));
                duallist.createDefaultButtons();
                duallist.setDataSourceFieldName("key", "value");

                NameValueCollection ardt = new NameValueCollection();
                if (curValue != "")
                {
                    string[] tmp = curValue.Split(',');
                    foreach (string key in tmp)
                    {
                        ardt[key] = field.arraySelectList[key];
                    }
                }
                ArrayDataSet ards2 = new ArrayDataSet(ardt, "value");

                duallist.setDataSource(ards.getIterator(), ards2.getIterator());

                XmlInputLabelObjects label = new XmlInputLabelObjects("=>");
                label.addXmlnukeObject(duallist);

                return label;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.FILE)
            {
                XmlInputFile inf = new XmlInputFile(field.fieldCaption, field.fieldName);
                return inf;
            }
            else if ((field.fieldXmlInput == XmlInputObjectType.DATE) || (field.fieldXmlInput == XmlInputObjectType.DATETIME))
            {
                string[] cur = curValue.Split(' ');
                string hour = "";
                if (cur.Length == 2)
                {
                    hour = cur[1];
                }
                XmlInputDateTime idt = new XmlInputDateTime(field.fieldCaption, field.fieldName, this._dateFormat, (field.fieldXmlInput == XmlInputObjectType.DATETIME), cur[0], hour);
                return idt;
            }
            else if (field.fieldXmlInput == XmlInputObjectType.FILEUPLOAD)
            {
                XmlInputFile file = new XmlInputFile(field.fieldCaption, field.fieldName);
                return file;
            }
            else
            {
                XmlInputLabelField xlf = new XmlInputLabelField(field.fieldCaption, curValue);
                return xlf;
            }
        }

        protected string redirProcessPage(bool full)
        {
            XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
            url.addParam("acao", ACTION_MSG);
            url.addParam(PARAM_MSG, this._currentAction);
            //url.addParam("filter", this._filter);
            //url.addParam("sort", this._sort);
            url.addParam("curpage", this._curPage);
            url.addParam("offset", this._qtdRows);
            foreach (string key in this._parameter.Keys)
            {
                url.addParam(key, this._parameter[key]);
            }
            //if (full)
            //{
            return url.getUrlFull(this._context);
            //}
            //else
            //{
            //	return url.getUrl();
            //}
        }


        /// <summary>
        /// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
        /// </summary>
        /// <param name="px">PageXml class</param>
        /// <param name="current">XmlNode where the XML will be created.</param>
        public override sealed void generateObject(XmlNode current)
        {
            // Improve Security
            bool wrongway = !this._edit && ((this._currentAction == ProcessPageStateBase.ACTION_EDIT) || (this._currentAction == ProcessPageStateBase.ACTION_EDIT_CONFIRM));
            wrongway = wrongway || (!this._new && ((this._currentAction == ProcessPageStateBase.ACTION_NEW) || (this._currentAction == ProcessPageStateBase.ACTION_NEW_CONFIRM)));
            wrongway = wrongway || (!this._delete && ((this._currentAction == ProcessPageStateBase.ACTION_DELETE) || (this._currentAction == ProcessPageStateBase.ACTION_DELETE_CONFIRM)));
            if (wrongway)
            {
                string message = this._lang.Value("MSG_DONT_HAVEGRANT");
                XmlParagraphCollection p = new XmlParagraphCollection();
                p.addXmlnukeObject(new XmlnukeText(message, true, true, false));
                p.generateObject(current);
                return;
            }

            // Checkings!
            if (this._context.ContextValue(PARAM_CANCEL) != "")
            {
                this.listAllRecords().generateObject(current);
            }
            else if (this._currentAction.EndsWith("_confirm"))
            {
                IXmlnukeDocumentObject validateRecord = null;
                try
                {
                    validateRecord = this.updateRecord();
                }
                catch (Exception ex)
                {
                    NameValueCollection nvc = new NameValueCollection();
                    nvc.Add("", ex.Message);
                    XmlParagraphCollection p = new XmlParagraphCollection();
                    p.addXmlnukeObject(new XmlEasyList(EasyListType.UNORDEREDLIST, "Error", this._lang.Value("ERR_FOUND"), nvc, ""));
                    XmlAnchorCollection a = new XmlAnchorCollection("javascript:history.go(-1)", "");
                    a.addXmlnukeObject(new XmlnukeText(this._lang.Value("TXT_BACK")));
                    p.addXmlnukeObject(a);
                    validateRecord = p;
                }
                if (validateRecord == null)
                {
                    this._context.redirectUrl(this.redirProcessPage(false));
                }
                else
                {
                    validateRecord.generateObject(current);
                    //if (this._currentAction != ProcessPageStateBase.ACTION_NEW_CONFIRM)
                    //{
                    //	this.showCurrentRecord().generateObject(current);
                    //}
                }
            }
            else if (this._currentAction == ACTION_MSG)
            {
                this.showResultMessage().generateObject(current);
                this.listAllRecords().generateObject(current);
            }
            else if ((this._currentAction == ACTION_NEW) || (this._currentAction == ACTION_VIEW) || (this._currentAction == ACTION_EDIT) || (this._currentAction == ACTION_DELETE))
            {
                this.showCurrentRecord().generateObject(current);
            }
            else
            {
                this.listAllRecords().generateObject(current);
            }
        }
    }



    public class ProcessPageStateBaseFormatterKey : IEditListFormatter
    {
        public string Format(SingleRow row, string fieldname, string value)
        {
            string[] fieldnameKey = fieldname.Split('|');
            value = "";
            foreach (string fieldnameValue in fieldnameKey)
            {
                value += ((value != "") ? "|" : "") + row.getField(fieldnameValue);
            }
            return value;
        }
    }

    public class ProcessPageStateBaseFormatterDualList : IEditListFormatter
    {
        protected NameValueCollection _arraySource;

        public ProcessPageStateBaseFormatterDualList(NameValueCollection arraySource)
        {
            this._arraySource = arraySource;
        }

        public string Format(SingleRow row, string fieldname, string value)
        {
            if (value != "")
            {
                string[] tmp = value.Split(',');
                string arResult = "";
                foreach (string item in tmp)
                {
                    arResult += (arResult != "" ? ", " : "") + this._arraySource[item];
                }
                return arResult;
            }
            else
            {
                return "-";
            }
        }
    }

    public class ProcessPageStateBaseSaveFormatterFileUpload : IEditListFormatter
    {
        protected Context _context;
        protected string _path = "";
        protected string _saveAs = "";

        public ProcessPageStateBaseSaveFormatterFileUpload(Context context, string path)
            : this(context, path, "*")
        { }

        public ProcessPageStateBaseSaveFormatterFileUpload(Context context, string path, string saveAs)
        {
            this._context = context;
            this._path = path;
            this._saveAs = saveAs;
        }

        public string Format(SingleRow row, string fieldname, string value)
        {
            ArrayList files = this._context.getUploadFileNames();

            if (files.Contains(fieldname))
            {
                UploadFilenameProcessor fileProcessor = new UploadFilenameProcessor(this._saveAs, this._context);
                fileProcessor.FilenameLocation = ForceFilenameLocation.DefinePath;
                fileProcessor.PathForced = this._path;

                // Salva os arquivos do formulário
                ArrayList result = this._context.processUpload(fileProcessor, (this._saveAs != "*"), fieldname);
                return (string)result[0];
            }
            else
            {
                return row.getField(fieldname);
            }
        }

    }

}
