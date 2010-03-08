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
using System.Xml;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using com.xmlnuke.classes;
using com.xmlnuke.processor;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.module
{
    public class Sample : BaseModule
    {
        /// <summary>
        /// Default constructor
        /// </summary>
        public Sample()
        { }

        /// <summary>
        /// Return the LanguageCollection used in this module
        /// </summary>
        /// <returns>LanguageCollection</returns>
        override public international.LanguageCollection WordCollection()
        {
            international.LanguageCollection myWords = base.WordCollection();

            if (!myWords.loadedFromFile())
            {
                // English Words
                myWords.addText("en-us", "TITLE", "Module Requested Not Found");
                myWords.addText("en-us", "MESSAGE", "The requested module {0}");
                // Portuguese Words
                myWords.addText("pt-br", "TITLE", "Módulo solicitado não foi encontrado");
                myWords.addText("pt-br", "MESSAGE", "O módulo solicitado {0}");
            }

            return myWords;
        }

        /// <summary>
        /// Returns if use cache
        /// </summary>
        /// <returns>False</returns>
        override public bool useCache()
        {
            return false;
        }

        /// <summary>
        /// Output error message
        /// </summary>
        /// <returns>PageXml object</returns>
        override public IXmlnukeDocument CreatePage()
        {
            // Não é mais necessário essa opção!
            //PageXml px = base.CreatePage();
            international.LanguageCollection myWords = this.WordCollection();

            XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Módulo de Demonstração das Novas Funcionalidades", "Esse módulo permite demonstrar das novas funcionalidades de programação do XMLNuke");
            xmlnukeDoc.setMenuTitle("Opções desse módulo");
            xmlnukeDoc.addMenuItem("url://module:sample?op=1", "Utilizando Objetos", "Demonstra a utilização dos novos objetos do Framework do XMLNuke");
            xmlnukeDoc.addMenuItem("url://module:sample?op=2", "Formulários", "Demonstra a utilização de formulários no Framework do XMLNuke");
            xmlnukeDoc.addMenuItem("url://module:sample?op=3", "Usando o EditList", "Demonstra como utilizar o EditList.");
            xmlnukeDoc.addMenuItem("url://module:sample?op=4", "Editando AnydataSet rapidamente", "Nova funcionalidade do XMLNuke para editar objetos (Anydataset e tabelas de banco de dados) rapidamente.");
            xmlnukeDoc.addMenuItem("url://module:sample?op=5", "Acessando Banco de Dados", "Nova função para acessar banco de dados");
            xmlnukeDoc.addMenuItem("url://module:sample?op=6", "Upload", "Utilizando o componente de Upload");
            xmlnukeDoc.addMenuItem("url://module:sample?op=7", "XmlDataSet", "Utilizando o XmlDataSet");
            xmlnukeDoc.addMenuItem("url://module:sample?op=8", "TextFileDataSet", "Utilizando o TextFileDataSet");
            xmlnukeDoc.addMenuItem("url://module:sample?op=9", "XmlChart", "Criando gráficos com o XML");
            xmlnukeDoc.addMenuItem("url://module:sample?op=10", "XmlnukeTabView", "XmlnukeTabView");
            xmlnukeDoc.addMenuItem("url://module:sample?op=11", "XmlDualList", "XmlDualList");
            xmlnukeDoc.addMenuItem("url://module:sample?op=12", "XmlnukeFaq", "XmlnukeFaq");
            xmlnukeDoc.addMenuItem("url://module:sample?op=13", "Ajax Post", "Ajax Post");
            xmlnukeDoc.addMenuItem("url://module:sample?op=14", "Auto Suggest", "Auto Suggest");
            xmlnukeDoc.addMenuItem("url://module:sample?op=15", "Treeview", "Treeview Component");
            xmlnukeDoc.addMenuItem("url://module:sample?op=16", "Sortable", "Sortable Component");
            xmlnukeDoc.addMenuItem("url://module:sample?op=17", "Calendar", "Calendar Component");
            xmlnukeDoc.addMenuItem("url://module:sample?op=18", "UI Alert", "UI Alert Component");
            xmlnukeDoc.addMenuItem("url://module:sample?op=19", "Media Gallery", "Media Gallery Component");

            XmlBlockCollection block = new XmlBlockCollection("Módulo de Demonstração das Novas Funcionalidades", BlockPosition.Center);

            XmlParagraphCollection p = new XmlParagraphCollection();
            p.addXmlnukeObject(new XmlnukeText("Esse módulo tem como objetivo apenas demonstrar as novas funcionalidades e classes do XMLNuke. Portanto, ao observar o código fonte, é possível ver como o programa roda."));
            p.addXmlnukeObject(new XmlnukeText("Selecione a opção que deseja executar no menu de opções."));

            block.addXmlnukeObject(p);
            xmlnukeDoc.addXmlnukeObject(block);

            int opcao;
            try
            {
                opcao = Convert.ToInt32(this._context.ContextValue("op"));
            }
            catch
            {
                opcao = 0;
            }

            switch (opcao)
            {
                case 1:
                    {
                        this.Opcao1(xmlnukeDoc);
                        break;
                    }
                case 2:
                    {
                        this.Opcao2(xmlnukeDoc);
                        break;
                    }
                case 3:
                    {
                        this.Opcao3(xmlnukeDoc);
                        break;
                    }
                case 4:
                    {
                        this.Opcao4(xmlnukeDoc);
                        break;
                    }
                case 5:
                    {
                        this.Opcao5(xmlnukeDoc);
                        break;
                    }
                case 6:
                    {
                        this.Opcao6(xmlnukeDoc);
                        break;
                    }
                case 7:
                    {
                        this.Opcao7(xmlnukeDoc);
                        break;
                    }
                case 8:
                    {
                        this.Opcao8(xmlnukeDoc);
                        break;
                    }
                case 9:
                    {
                        this.Opcao9(xmlnukeDoc);
                        break;
                    }
                case 10:
                    {
                        this.Opcao10(xmlnukeDoc);
                        break;
                    }
                case 11:
                    {
                        this.Opcao11(xmlnukeDoc);
                        break;
                    }
                case 12:
                    {
                        this.Opcao12(xmlnukeDoc);
                        break;
                    }
                case 13:
                    {
                        this.Opcao13(xmlnukeDoc);
                        break;
                    }
                case 14:
                    {
                        this.Opcao14(xmlnukeDoc);
                        break;
                    }
                case 15:
                    {
                        this.Opcao15(xmlnukeDoc);
                        break;
                    }
                case 16:
                    {
                        this.Opcao16(xmlnukeDoc);
                        break;
                    }
                case 17:
                    {
                        this.Opcao17(xmlnukeDoc);
                        break;
                    }
                case 18:
                    {
                        this.Opcao18(xmlnukeDoc);
                        break;
                    }
                case 19:
                    {
                        this.Opcao19(xmlnukeDoc);
                        break;
                    }
            }



            return xmlnukeDoc.generatePage();

        }


        protected void Opcao1(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 1: Criação dos Objetos", BlockPosition.Center);

            XmlnukeBreakLine br = new XmlnukeBreakLine();

            // Cria um paragrafo
            XmlParagraphCollection para1 = new XmlParagraphCollection();
            para1.addXmlnukeObject(new XmlnukeText("Com o novo modelo de objetos do XMLNuke é possível criar os objetos e adicioná-los da maneira que desejar dentro das coleções de objetos."));
            para1.addXmlnukeObject(br);
            para1.addXmlnukeObject(new XmlnukeText("Também é possível criar rapidamente textos em itálico, negrito e sublinhado.", true, true, true, true));
            para1.addXmlnukeObject(br);
            para1.addXmlnukeObject(new XmlnukeText("Ou simplesmente escrever o texto. Note que esses objetos foram criados uma única vez e dispostos nas blocos a esquerda, direita e centro"));
            //list

            XmlParagraphCollection para2 = new XmlParagraphCollection();
            para2.addXmlnukeObject(new XmlnukeText("O objeto de HiperLink pode agora conter outros objetos. "));
            para2.addXmlnukeObject(br);
            para2.addXmlnukeObject(br);
            XmlnukeImage xmlImg = new XmlnukeImage("common/imgs/logo_xmlnuke.gif");
            XmlAnchorCollection href = new XmlAnchorCollection("engine:xmlnuke", "");
            href.addXmlnukeObject(new XmlnukeText("Clique -."));
            href.addXmlnukeObject(br);
            href.addXmlnukeObject(xmlImg);
            href.addXmlnukeObject(br);
            href.addXmlnukeObject(new XmlnukeText("<-- Clique"));
            para2.addXmlnukeObject(href);
            para2.addXmlnukeObject(br);

            // Trabalhar com EasyList
            XmlParagraphCollection para3 = new XmlParagraphCollection();
            System.Collections.Specialized.NameValueCollection nvc = new System.Collections.Specialized.NameValueCollection();
            nvc.Add("OP1", "Opção número 1 de teste");
            nvc.Add("OP2", "Opção número 2 de teste");
            nvc.Add("OP3", "Opção número 3 de teste");
            nvc.Add("OP4", "Opção número 4 de teste");
            para3.addXmlnukeObject(new XmlEasyList(EasyListType.UNORDEREDLIST, "name", "caption", nvc, "OP3"));
            //form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "name", "caption", nvc, "OP3"));
            //form.addXmlnukeObject(new XmlEasyList(EasyListType.CHECKBOX, "name", "caption", nvc, "OP3"));
            //form.addXmlnukeObject(new XmlEasyList(EasyListType.RADIOBOX, "name", "caption", nvc, "OP3"));


            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(para2);
            block.addXmlnukeObject(para3);


            XmlBlockCollection block2 = new XmlBlockCollection("Bloco Esquerda", BlockPosition.Left);
            block2.addXmlnukeObject(para1);

            XmlBlockCollection block3 = new XmlBlockCollection("Bloco Direita", BlockPosition.Right);
            block3.addXmlnukeObject(para1);

            xmlnukeDoc.addXmlnukeObject(block);
            xmlnukeDoc.addXmlnukeObject(block2);
            xmlnukeDoc.addXmlnukeObject(block3);

        }


        protected void Opcao2(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 2: Formulários", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();

            XmlParagraphCollection para1 = new XmlParagraphCollection();
            para1.addXmlnukeObject(new XmlnukeText("A criação de objetos de edição de formulário também fica transparente ao usuário."));

            // Cria um Formulário
            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample", "Formulário de Edição");
            form.setJSValidate(true);
            form.addXmlnukeObject(new XmlInputHidden("op", "2"));
            form.addXmlnukeObject(new XmlInputLabelField("Caption", "Value"));
            XmlInputTextBox text = new XmlInputTextBox("Campo Obrigatorio", "campo1", "");
            text.setRequired(true);
            form.addXmlnukeObject(text);

            XmlInputDateTime datetime = new XmlInputDateTime("Date Picker", "date1", DATEFORMAT.DMY, false);
            form.addXmlnukeObject(datetime);

            datetime = new XmlInputDateTime("Date Picker", "date2", DATEFORMAT.YMD, true);
            form.addXmlnukeObject(datetime);

            XmlInputTextBox text2 = new XmlInputTextBox("Campo do tipo email", "campo2", "");
            text2.setRequired(true);
            text2.setDataType(INPUTTYPE.EMAIL);
            form.addXmlnukeObject(text2);
            form.addXmlnukeObject(new XmlInputMemo("Memorando", "campo3", "Value"));
            form.addXmlnukeObject(new XmlInputCheck("Checkbox", "check1", "Value"));
            XmlInputCheck ic = new XmlInputCheck("Caption ReadOnly:", "check2", "Valor");
            ic.setChecked(true);
            ic.setReadOnly(true);
            form.addXmlnukeObject(ic);
            XmlInputTextBox itb = new XmlInputTextBox("Input ReadOnly:", "campo4", "Valor");
            itb.setReadOnly(true);
            form.addXmlnukeObject(itb);
            XmlInputButtons buttons = new XmlInputButtons();
            buttons.addSubmit("Submit", "bs");
            buttons.addReset("Reset", "br");
            buttons.addButton("Button", "bt", "javascript:alert('ok')");
            form.addXmlnukeObject(buttons);


            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);

        }

        protected void Opcao3(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 3: Usando o EditList", BlockPosition.Center);

            XmlnukeBreakLine br = new XmlnukeBreakLine();

            XmlParagraphCollection para1 = new XmlParagraphCollection();
            para1.addXmlnukeObject(new XmlnukeText("O EditList é um TAG XML definido pelo XMLNuke que tem a funcão de listar os objetos de forma de listagem. Além disso habilita a função de selecionar uma ou mais linhas e executar uma ação. "));
            para1.addXmlnukeObject(new XmlnukeText("Essa ação deve ser tratada separadamente.", true, false, false));
            para1.addXmlnukeObject(br);

            processor.AnydatasetFilenameProcessor guestbookFile = new processor.AnydatasetFilenameProcessor("guestbook", this._context);
            anydataset.AnyDataSet guestbook = new anydataset.AnyDataSet(guestbookFile);
            anydataset.Iterator it = guestbook.getIterator();

            XmlParagraphCollection para3 = new XmlParagraphCollection();
            XmlEditList editList = new XmlEditList(this._context, "Conteúdo do Livro de Visitas", "module:sample");
            editList.setDataSource(it);
            editList.addParameter("op", "3");


            EditListField field;

            field = new EditListField(true);
            field.fieldData = "frommail"; // Esse é o campo CHAVE que contém o VALUEID
            editList.addEditListField(field);

            field = new EditListField(true);
            field.fieldData = "fromname";
            field.editlistName = "Nome";

            field = new EditListField(true);
            field.fieldData = "frommail";
            field.editlistName = "Email";
            editList.addEditListField(field);

            field = new EditListField(true);
            field.fieldData = "message";
            field.editlistName = "Mensagem Postada";
            editList.addEditListField(field);

            CustomButtons cb = new CustomButtons();
            cb.action = "acaocustomizada";
            cb.enabled = true;
            cb.alternateText = "Texto alternativo da ação";
            cb.url = this._context.bindModuleUrl("sample") + "&op=3";
            cb.icon = "common/editlist/ic_custom.gif";
            editList.setCustomButton(0, cb);
            editList.setPageSize(3, 0);
            editList.setEnablePage(true);
            para3.addXmlnukeObject(editList);

            XmlParagraphCollection para2 = new XmlParagraphCollection();
            para2.addXmlnukeObject(new XmlnukeText("Ação selecionada: ", true, false, false));
            para2.addXmlnukeObject(new XmlnukeText(this._action));
            para2.addXmlnukeObject(br);
            para2.addXmlnukeObject(new XmlnukeText("Valor selecionado: ", true, false, false));
            para2.addXmlnukeObject(new XmlnukeText(this._context.ContextValue("valueid")));

            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(para3);
            if (this._action != "")
            {
                block.addXmlnukeObject(para2);
            }

            xmlnukeDoc.addXmlnukeObject(block);

        }

        protected void Opcao4(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 4: Editando rapidamente documentos Anydataset", BlockPosition.Center);

            XmlnukeBreakLine br = new XmlnukeBreakLine();

            XmlParagraphCollection para1 = new XmlParagraphCollection();
            para1.addXmlnukeObject(new XmlnukeText("Através dessa coleção, é possível criar telas de edição, inclusão e exclusão rapidamente sem a necessidade de se fazer diversas programações. Para isso, é necessário definir um dicionário de dados e instanciar uma classe herdade de "));
            para1.addXmlnukeObject(new XmlnukeText("ProcessPageStateBase.", true, false, false));
            para1.addXmlnukeObject(br);

            // Cria um acesso a processPage
            ProcessPageField fieldPage;
            ProcessPageFields pageFields = new ProcessPageFields();

            fieldPage = new ProcessPageField(true);
            fieldPage.fieldName = "code";
            fieldPage.key = true;
            fieldPage.dataType = INPUTTYPE.NUMBER;
            fieldPage.fieldCaption = "Código";
            fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
            fieldPage.visibleInList = true;
            fieldPage.editable = true;
            fieldPage.required = true;
            fieldPage.rangeMin = "100";
            fieldPage.rangeMax = "10000";
            pageFields.addProcessPageField(fieldPage);

            fieldPage = new ProcessPageField(true);
            fieldPage.fieldName = "name";
            fieldPage.key = false;
            fieldPage.dataType = INPUTTYPE.TEXT;
            fieldPage.fieldCaption = "Nome";
            fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
            fieldPage.visibleInList = true;
            fieldPage.editable = true;
            fieldPage.required = true;
            pageFields.addProcessPageField(fieldPage);

            fieldPage = new ProcessPageField(true);
            fieldPage.fieldName = "email";
            fieldPage.key = false;
            fieldPage.dataType = INPUTTYPE.EMAIL;
            fieldPage.fieldCaption = "Email";
            fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
            fieldPage.visibleInList = true;
            fieldPage.editable = true;
            fieldPage.required = true;
            pageFields.addProcessPageField(fieldPage);

            fieldPage = new ProcessPageField(true);
            fieldPage.fieldName = "data";
            fieldPage.key = false;
            fieldPage.dataType = INPUTTYPE.DATE;
            fieldPage.fieldCaption = "Data";
            fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
            fieldPage.visibleInList = false;
            fieldPage.editable = true;
            fieldPage.required = true;
            fieldPage.size = 10;
            pageFields.addProcessPageField(fieldPage);

            fieldPage = new ProcessPageField(true);
            fieldPage.fieldName = "Memo";
            fieldPage.key = false;
            fieldPage.dataType = INPUTTYPE.TEXT;
            fieldPage.fieldCaption = "Memorando";
            fieldPage.fieldXmlInput = XmlInputObjectType.MEMO;
            fieldPage.visibleInList = false;
            fieldPage.editable = true;
            fieldPage.required = false;
            pageFields.addProcessPageField(fieldPage);

            //ProcessPageStateDB processPage = new ProcessPageStateDB(this._context, fieldPage, "Edição teste usando Banco de Dados", "module:sample", null, "teste", "intercon2");
            ProcessPageStateAnydata processPage =
                new ProcessPageStateAnydata(
                        this._context,
                        pageFields,
                        "Edição teste usando Banco de Dados",
                        "module:sample?op=4",
                        null,
                        new com.xmlnuke.processor.AnydatasetFilenameProcessor("sample", this._context));
            processPage.setPageSize(3, 0);
            para1.addXmlnukeObject(processPage);


            block.addXmlnukeObject(para1);

            xmlnukeDoc.addXmlnukeObject(block);

        }

        protected void Opcao5(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 5: Acessando e Manipulando banco de dados", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();

            XmlParagraphCollection para1 = new XmlParagraphCollection();

            string secop = this._context.ContextValue("secop");

            // Menu
            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample?op=5", "Menu");
            System.Collections.Specialized.NameValueCollection optionlist = new System.Collections.Specialized.NameValueCollection();
            optionlist.Add("", "-- Selecione --");
            optionlist.Add("setup", "Configurar a Conexão");
            optionlist.Add("test", "Testar a conexão");
            optionlist.Add("create", "Create Sample Table");
            optionlist.Add("edit", "Edit Sample Table");
            XmlEasyList list = new XmlEasyList(EasyListType.SELECTLIST, "secop", "Selecione a Ação", optionlist, secop);
            form.addXmlnukeObject(list);

            XmlInputButtons btnmenu = new XmlInputButtons();
            btnmenu.addSubmit("Selecionar");
            form.addXmlnukeObject(btnmenu);
            block.addXmlnukeObject(form);

            // Opções:
            switch (secop)
            {
                case "setup":
                    {
                        XmlFormCollection formsetup = new XmlFormCollection(this._context, "module:sample?op=5", "Editar Conexão");
                        formsetup.addXmlnukeObject(new XmlInputHidden("secop", "setupconf"));
                        System.Collections.Specialized.NameValueCollection dblist = new System.Collections.Specialized.NameValueCollection();
                        dblist.Add("", "-- Selecione --");
                        dblist.Add("ODBC", "ODBC");
                        dblist.Add("OLEDB", "OLEDB");
                        dblist.Add("SQLSERVER", "SQLSERVER");
                        dblist.Add("MYSQL", "MYSQL");
                        dblist.Add("ORACLE", "ORACLE");
                        dblist.Add("POSTGRES", "POSTGRES");
                        dblist.Add("FIREBIRD", "FIREBIRD");
                        dblist.Add("SQLITE", "SQLITE");
                        XmlEasyList list2 = new XmlEasyList(EasyListType.SELECTLIST, "dbtype", "DbType", dblist);
                        list.setRequired(true);
                        formsetup.addXmlnukeObject(list2);

                        XmlInputTextBox text = new XmlInputTextBox("Connection String", "connection", "Data Source=localhost;User Id=username;Password=pass;Database=database;");
                        text.setRequired(true);
                        formsetup.addXmlnukeObject(text);

                        XmlInputButtons btn = new XmlInputButtons();
                        btn.addSubmit("Salvar");
                        formsetup.addXmlnukeObject(btn);
                        block.addXmlnukeObject(formsetup);
                        break;
                    }
                case "setupconf":
                    {
                        AnydatasetFilenameProcessor filename = new AnydatasetFilenameProcessor("_db", this._context);
                        anydataset.AnyDataSet anydata = new com.xmlnuke.anydataset.AnyDataSet(filename);
                        anydataset.IteratorFilter itf = new com.xmlnuke.anydataset.IteratorFilter();
                        itf.addRelation("dbname", com.xmlnuke.anydataset.Relation.Equal, "sampledb");
                        anydataset.Iterator it = anydata.getIterator(itf);
                        if (it.hasNext())
                        {
                            anydataset.SingleRow sr = it.moveNext();
                            sr.setField("dbtype", this._context.ContextValue("dbtype"));
                            sr.setField("dbconnectionstring", this._context.ContextValue("connection"));
                        }
                        else
                        {
                            anydata.appendRow();
                            anydata.addField("dbname", "sampledb");
                            anydata.addField("dbtype", this._context.ContextValue("dbtype"));
                            anydata.addField("dbconnectionstring", this._context.ContextValue("connection"));
                        }
                        anydata.Save();
                        para1.addXmlnukeObject(new XmlnukeText("Updated!", true));
                        break;
                    }
                case "test":
                    {
                        anydataset.DBDataSet db = new com.xmlnuke.anydataset.DBDataSet("sampledb", this._context);
                        db.TestConnection();
                        para1.addXmlnukeObject(new XmlnukeText("I suppose it is fine the connection string!", true));
                        break;
                    }
                case "create":
                    {
                        anydataset.DBDataSet db = new com.xmlnuke.anydataset.DBDataSet("sampledb", this._context);
                        string sql = "create table sample (fieldkey integer, fieldname varchar(20))";
                        db.execSQL(sql);
                        db.TestConnection();
                        para1.addXmlnukeObject(new XmlnukeText("Table Created!", true));
                        break;
                    }
                case "edit":
                    {
                        // Cria um acesso a processPage
                        ProcessPageField fieldPage;
                        ProcessPageFields pageFields = new ProcessPageFields();

                        fieldPage = new ProcessPageField(true);
                        fieldPage.fieldName = "fieldkey";
                        fieldPage.key = true;
                        fieldPage.dataType = INPUTTYPE.NUMBER;
                        fieldPage.fieldCaption = "Código";
                        fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
                        fieldPage.visibleInList = true;
                        fieldPage.editable = true;
                        fieldPage.required = true;
                        fieldPage.rangeMin = "100";
                        fieldPage.rangeMax = "999";
                        pageFields.addProcessPageField(fieldPage);

                        fieldPage = new ProcessPageField(true);
                        fieldPage.fieldName = "fieldname";
                        fieldPage.key = false;
                        fieldPage.dataType = INPUTTYPE.TEXT;
                        fieldPage.fieldCaption = "Name";
                        fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
                        fieldPage.visibleInList = true;
                        fieldPage.editable = true;
                        fieldPage.required = true;
                        fieldPage.maxLength = 20;
                        pageFields.addProcessPageField(fieldPage);

                        ProcessPageStateDB processPage =
                            new ProcessPageStateDB(
                                    this._context,
                                    pageFields,
                                    "Edição teste usando Banco de Dados",
                                    "module:sample?op=5", null,
                                    "sample",
                                    "sampledb");
                        processPage.setPageSize(3, 0);
                        processPage.addParameter("secop", "edit");
                        para1.addXmlnukeObject(processPage);
                        break;
                    }
            }


            block.addXmlnukeObject(para1);

            xmlnukeDoc.addXmlnukeObject(block);

        }

        protected void Opcao6(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 6: Upload de Documentos", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para1 = new XmlParagraphCollection();

            if (this._context.ContextValue("posted") != "")
            {
                UploadFilenameProcessor uploadFilename = new UploadFilenameProcessor("common" + util.FileUtil.Slash() + "files", this._context);
                uploadFilename.FilenameLocation = ForceFilenameLocation.SharedPath;

                ArrayList files = this._context.processUpload(uploadFilename, false);

                //this._context.Debug(files);

                para1.addXmlnukeObject(new XmlnukeText("A criação de objetos de edição de formulário também fica transparente ao usuário."));
            }

            // Cria um Formulário
            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample", "Formulário de Edição");
            form.addXmlnukeObject(new XmlInputHidden("op", "6"));
            form.addXmlnukeObject(new XmlInputHidden("posted", "true"));
            XmlInputFile inf = new XmlInputFile("Digite o arquivo para Upload: ", "filetoupload");
            form.addXmlnukeObject(inf);
            XmlInputButtons buttons = new XmlInputButtons();
            buttons.addSubmit("Submit", "bs");
            form.addXmlnukeObject(buttons);


            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);

        }


        protected void Opcao7(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 7: XmlDataSet", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para1 = new XmlParagraphCollection();

            string xmlstr = this._context.ContextValue("xmlstr");
            string rowNode = this._context.ContextValue("rownode");
            string[] colNodeStr = this._context.ContextValue("cols").Split('\n');
            if (xmlstr != "")
            {
                System.Collections.Specialized.NameValueCollection colNode = new System.Collections.Specialized.NameValueCollection();
                foreach (string s in colNodeStr)
                {
                    string[] tmp = s.Split('=');
                    colNode.Add(tmp[0], tmp[1].Replace("\r", ""));
                }

                com.xmlnuke.anydataset.XmlDataSet dataset = new com.xmlnuke.anydataset.XmlDataSet(this._context, xmlstr, rowNode, colNode);
                //para1.addXmlnukeObject(new XmlnukeText(""));
                XmlEditList editlist = new XmlEditList(this._context, "XML Flat", "module:sample?op=7");
                editlist.setReadOnly(true);
                editlist.setDataSource(dataset.getIterator());
                para1.addXmlnukeObject(editlist);
            }
            else
            {
                AnydatasetFilenameProcessor processor = new AnydatasetFilenameProcessor("sample", this._context);
                xmlstr = util.FileUtil.QuickFileRead(processor.PathSuggested() + "sample.xml");
                rowNode = "book";
                colNodeStr = new string[]
					{
						"category=@category",
						"title=title",
						"titlelang=title/@lang",
						"year=year",
						"price=price",
						"author=author"
					};
            }

            // Cria um Formulário
            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample", "Formulário de Edição");

            form.addXmlnukeObject(new XmlInputHidden("op", "7"));
            XmlInputMemo memo = new XmlInputMemo("XML", "xmlstr", xmlstr);
            form.addXmlnukeObject(memo);

            XmlInputTextBox text = new XmlInputTextBox("Row XPath", "rownode", rowNode);
            text.setRequired(true);
            form.addXmlnukeObject(text);

            XmlInputMemo colMemo = new XmlInputMemo("Col XPath", "cols", String.Join("\n", colNodeStr));
            form.addXmlnukeObject(colMemo);

            XmlInputButtons buttons = new XmlInputButtons();
            buttons.addSubmit("Submit", "bs");
            form.addXmlnukeObject(buttons);


            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao8(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 8: TextFileDataSet", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para1 = new XmlParagraphCollection();

            string txtstr = this._context.ContextValue("txtstr");
            string regexp = this._context.ContextValue("regexp");
            string[] colNodeStr = this._context.ContextValue("cols").Split('\n');
            if (txtstr != "")
            {
                AnydatasetFilenameProcessor processor = new AnydatasetFilenameProcessor("sample", this._context);
                util.FileUtil.QuickFileWrite(processor.PathSuggested() + "sample.csv", txtstr);
                com.xmlnuke.anydataset.TextFileDataSet dataset = new com.xmlnuke.anydataset.TextFileDataSet(this._context, processor.PathSuggested() + "sample.csv", colNodeStr, regexp);
                //para1.addXmlnukeObject(new XmlnukeText(""));
                XmlEditList editlist = new XmlEditList(this._context, "Text Flat", "module:sample?op=8");
                editlist.setReadOnly(true);
                editlist.setDataSource(dataset.getIterator());
                para1.addXmlnukeObject(editlist);
            }
            else
            {
                AnydatasetFilenameProcessor processor = new AnydatasetFilenameProcessor("sample", this._context);
                txtstr = util.FileUtil.QuickFileRead(processor.PathSuggested() + "sample.csv");
                regexp = com.xmlnuke.anydataset.TextFileDataSet.CSVFILE;
                colNodeStr = new string[]
					{
						"category",
						"title",
						"titlelang",
						"year",
						"price",
						"buyprice",
						"author"
					};
            }

            // Cria um Formulário
            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample", "Formulário de Edição");

            form.addXmlnukeObject(new XmlInputHidden("op", "8"));
            XmlInputMemo memo = new XmlInputMemo("Text", "txtstr", txtstr);
            form.addXmlnukeObject(memo);

            XmlInputTextBox text = new XmlInputTextBox("Regular Expression", "regexp", regexp);
            text.setRequired(true);
            form.addXmlnukeObject(text);

            XmlInputMemo colMemo = new XmlInputMemo("Col Names", "cols", String.Join("\n", colNodeStr));
            form.addXmlnukeObject(colMemo);

            XmlInputButtons buttons = new XmlInputButtons();
            buttons.addSubmit("Submit", "bs");
            form.addXmlnukeObject(buttons);


            block.addXmlnukeObject(para1);
            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao9(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 9: XmlChart", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para1 = new XmlParagraphCollection();

            string[] colNodeStr = new string[]
					{
						"category",
						"title",
						"titlelang",
						"year",
						"price",
						"buyprice",
						"author"
					};
            AnydatasetFilenameProcessor processor = new AnydatasetFilenameProcessor("sample", this._context);
            com.xmlnuke.anydataset.TextFileDataSet dataset = new com.xmlnuke.anydataset.TextFileDataSet(this._context, processor.PathSuggested() + "sample.csv", colNodeStr);
            //para1.addXmlnukeObject(new XmlnukeText(""));
            XmlEditList editlist = new XmlEditList(this._context, "Text Flat", "module:sample?op=9");
            editlist.setReadOnly(true);
            editlist.setDataSource(dataset.getIterator());
            para1.addXmlnukeObject(editlist);

            XmlChart chart = new XmlChart(this._context, "Book Store", dataset.getIterator(), ChartOutput.Flash, ChartSeriesFormat.Column);
            chart.setLegend("category", "#000000", "#C0C0C0");
            chart.addSeries("price", "Sell Price", "#000000");
            chart.addSeries("buyprice", "Buy Price", "#000000");
            para1.addXmlnukeObject(chart);

            XmlnukeCode code = new XmlnukeCode("Code Sample");
            code.AddTextLine("XmlChart chart = new XmlChart(");
            code.AddTextLine("		this._context,             // Xmlnuke Context");
            code.AddTextLine("		\"Book Store\",            // Graph Title");
            code.AddTextLine("		dataset.getIterator(),     // IIterator Object");
            code.AddTextLine("		ChartOutput.Flash,         // Graph output format ");
            code.AddTextLine("		ChartSeriesFormat.Column   // Default column type");
            code.AddTextLine(");");
            code.AddTextLine("chart.setLegend(\"category\", \"#000000\", \"#C0C0C0\");");
            code.AddTextLine("chart.addSeries(\"price\", \"Sell Price\", \"#000000\");");
            para1.addXmlnukeObject(code);

            block.addXmlnukeObject(para1);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao10(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 10: TabView", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para1 = new XmlParagraphCollection();
            para1.addXmlnukeObject(new XmlnukeText("Parágrafo 1"));

            XmlParagraphCollection para2 = new XmlParagraphCollection();
            para2.addXmlnukeObject(new XmlnukeText("Parágrafo 2"));

            XmlParagraphCollection para3 = new XmlParagraphCollection();
            para3.addXmlnukeObject(new XmlnukeText("Parágrafo 3"));

            XmlnukeTabView tabview = new XmlnukeTabView();
            tabview.addTabItem("Aba 1", para1);
            tabview.addTabItem("Aba 2", para2);
            tabview.addTabItem("Aba 3", para3);

            block.addXmlnukeObject(tabview);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao11(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 11: DualList", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();

            if (this.isPostBack())
            {
                util.Debug.Print(XmlDualList.Parse(this._context, "frmdual"));
            }


            XmlFormCollection form = new XmlFormCollection(this._context, "module:sample?op=11", "Formulário com Um Dual List");

            // Create DualList Object
            XmlDualList duallist = new XmlDualList(this._context, "frmdual", "Não Selecionado", "Selecionado");
            duallist.setDualListSize(10, 10);
            duallist.createDefaultButtons();

            // Define DataSet Source
            NameValueCollection arrayLeft = new NameValueCollection();
            arrayLeft.Add("A", "Letra A");
            arrayLeft.Add("B", "Letra B");
            arrayLeft.Add("C", "Letra C");
            arrayLeft.Add("D", "Letra D");
            arrayLeft.Add("E", "Letra E");
            arrayLeft.Add("F", "Letra F");
            NameValueCollection arrayRight = new NameValueCollection();
            arrayRight.Add("B", "Letra B");
            arrayRight.Add("D", "Letra D");
            ArrayDataSet arrayDBLeft = new ArrayDataSet(arrayLeft);
            IIterator itLeft = arrayDBLeft.getIterator();
            ArrayDataSet arrayDBRight = new ArrayDataSet(arrayRight);
            IIterator itRight = arrayDBRight.getIterator();
            duallist.setDataSourceFieldName("key", "value");
            duallist.setDataSource(itLeft, itRight);

            form.addXmlnukeObject(duallist);

            XmlInputButtons button = new XmlInputButtons();
            button.addSubmit("Enviar Dados");
            form.addXmlnukeObject(button);

            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);
        }


        protected void Opcao12(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 10: TabView", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlnukeSpanCollection para1 = new XmlnukeSpanCollection();
            para1.addXmlnukeObject(new XmlnukeText("Parágrafo 1"));

            XmlnukeSpanCollection para2 = new XmlnukeSpanCollection();
            para2.addXmlnukeObject(new XmlnukeText("Parágrafo 2"));

            XmlnukeSpanCollection para3 = new XmlnukeSpanCollection();
            para3.addXmlnukeObject(new XmlnukeText("Parágrafo 3"));

            XmlnukeFaq faq = new XmlnukeFaq("Lista de Perguntas");
            faq.addFaqItem("Pergunta 1", para1);
            faq.addFaqItem("Pergunta 2", para2);
            faq.addFaqItem("Pergunta 3", para3);

            block.addXmlnukeObject(faq);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao13(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 13: Ajax Post", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Com o XMLNuke é possível fazer um POST sem dar um refresh na página. É possível também definir uma área para exibir a resposta."));
            para.addXmlnukeObject(new XmlnukeText("Útil para fazer Uploads ou processamentos em background."));
            block.addXmlnukeObject(para);

            // First Create the FORM
            XmlFormCollection form = new XmlFormCollection(this._context, "xmlnuke.aspx?site=sample&xsl=preview", "Ajax Post");

            XmlInputTextBox txt = new XmlInputTextBox("Algum Texto", "name", "");
            txt.setRequired(true);
            form.addXmlnukeObject(txt);

            // And Add a Submit Button
            XmlInputButtons button = new XmlInputButtons();
            button.addSubmit("Teste");
            form.addXmlnukeObject(button);

            block.addXmlnukeObject(form);

            para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeBreakLine());
            para.addXmlnukeObject(new XmlnukeBreakLine());
            para.addXmlnukeObject(new XmlnukeBreakLine());
            para.addXmlnukeObject(new XmlnukeBreakLine());

            // Second, Create a AjaxCallBack, associate it our form
            XmlnukeAjaxCallback ajax = new XmlnukeAjaxCallback(this._context);
            ajax.setCustomStyle(400, true);
            form.setAjaxCallback(ajax);
            para.addXmlnukeObject(ajax);
            block.addXmlnukeObject(para);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao14(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 14: Auto Suggest", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("É possível associar um TextBox e uma consulta com auto sugestão de valores. Nesse exemplo, estamos consultando TODOS os links <A> existentes na página que é exibido como XML."));
            block.addXmlnukeObject(para);

            XmlFormCollection form = new XmlFormCollection(this._context, "", "Teste");

            form.addXmlnukeObject(new XmlInputCaption("Auto Suggest"));

            // First Create the the TextBox
            XmlInputTextBox txt = new XmlInputTextBox("Teste", "Nome", "");
            txt.setRequired(true);
            // and then associate the AutoSuggest
            txt.setAutosuggest(this._context, "module:sample?site=sample&rawxml=true&xpath=//a&", "input");
            form.addXmlnukeObject(txt);

            form.addXmlnukeObject(new XmlInputCaption("Auto Suggest com CallBack"));

            // First Create the the TextBox
            txt = new XmlInputTextBox("Teste", "Nome2", "");
            txt.setRequired(true);
            txt.setAutosuggest(this._context, "module:sample?site=sample&rawxml=true&xpath=//a&", "input", "nodeinfo", "nodeid", "alert(obj.id + ' - ' + obj.info + ' - ' + obj.value)");
            form.addXmlnukeObject(txt);


            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao15(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 15: Tree View", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
            block.addXmlnukeObject(para);

            XmlnukeTreeview treeview = new XmlnukeTreeview(this._context, "Título");

            XmlnukeTreeViewFolder folder1 = new XmlnukeTreeViewFolder(this._context, "Folder 1", "mydocuments.gif");
            folder1.setAction(TreeViewActionType.ExecuteJS, "document.getElementById('here').style.display='none';");

            XmlnukeTreeViewLeaf leaf = new XmlnukeTreeViewLeaf(this._context, "Leaf 1", "empty_doc.gif");
            leaf.setAction(TreeViewActionType.OpenUrl, "module:sample?op=1");
            folder1.addChild(leaf);
            leaf = new XmlnukeTreeViewLeaf(this._context, "Leaf 2", "empty_doc.gif");
            leaf.setAction(TreeViewActionType.OpenInNewWindow, "module:sample?op=2");
            folder1.addChild(leaf);
            leaf = new XmlnukeTreeViewLeaf(this._context, "Leaf 3", "document.gif");
            leaf.setAction(TreeViewActionType.OpenUrlInsideContainer, "module:sample?op=3&xsl=blank", "here");
            folder1.addChild(leaf);
            treeview.addChild(folder1);

            XmlnukeTreeViewFolder folder2 = new XmlnukeTreeViewFolder(this._context, "Folder 1", "myimages.gif");
            folder2.addChild(new XmlnukeTreeViewLeaf(this._context, "Leaf 1", "document.gif"));
            treeview.addChild(folder2);

            block.addXmlnukeObject(treeview);

            XmlContainerCollection container = new XmlContainerCollection("here");
            container.setStyle("display: none; width: 100%; height: 200px");
            block.addXmlnukeObject(container);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao16(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 16: Sortable", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
            block.addXmlnukeObject(para);

            XmlFormCollection form = new XmlFormCollection(this._context, "", "Sortable Example");
            XmlInputSortableList sortable = new XmlInputSortableList("Teste", "meunome");
            sortable.addSortableItem("1", new XmlnukeText("Teste 1"));
            sortable.addSortableItem("2", new XmlnukeText("Teste 2"));
            sortable.addSortableItem("3", new XmlnukeText("Teste 3"), SortableListItemState.Highligth);
            sortable.addSortableItem("4", new XmlnukeText("Teste 4"), SortableListItemState.Disabled);
            sortable.addSortableItem("5", new XmlnukeText("Teste 5"));
            sortable.addSortableItem("6", new XmlnukeText("Teste 6"));

            form.addXmlnukeObject(sortable);

            block.addXmlnukeObject(form);
            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao17(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 17: Calendar", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
            block.addXmlnukeObject(para);

            XmlnukeCalendar calendar = new XmlnukeCalendar(1, 1974);
            calendar.addCalendarEvent(new XmlnukeCalendarEvent(15, 1, "Teste"));
            calendar.addCalendarEvent(new XmlnukeCalendarEvent(26, 2, "Teste"));

            block.addXmlnukeObject(calendar);
            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao18(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 18: UI Alert", BlockPosition.Center);

            //XmlnukeBreakLine br = new XmlnukeBreakLine();


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como mostrar uma mensagem de alert no cliente"));
            block.addXmlnukeObject(para);

            XmlnukeUIAlert uialert = new XmlnukeUIAlert(this._context, UIAlert.BoxInfo);

            if (this._context.ContextValue("type") != "")
            {
                switch (this._context.ContextValue("type"))
                {
                    case "1":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.Dialog, "Isso é um teste");
                        break;
                    case "2":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.ModalDialog, "Isso é um teste");
                        uialert.setAutoHide(10000);
                        break;
                    case "3":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.ModalDialog, "Isso é um teste");
                        uialert.addRedirectButton("Ok", "module:sample");
                        uialert.addCloseButton("Cancel");
                        break;
                    case "4":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.ModalDialog, "Isso é um teste");
                        uialert.addRedirectButton("Ok, proceed!", "module:sample");
                        uialert.addCloseButton("Cancel");
                        uialert.setOpenAction(UIAlertOpenAction.Button, "Clique me");
                        break;
                    case "5":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.BoxInfo, "Isso é um teste");
                        uialert.setAutoHide(2000);
                        break;
                    case "6":
                        uialert = new XmlnukeUIAlert(this._context, UIAlert.BoxAlert, "Isso é um teste");
                        break;
                }
                uialert.addXmlnukeObject(new XmlnukeText("Isso é um novo teste, novo teste"));
                block.addXmlnukeObject(uialert);
            }

            Dictionary<string, string> list = new Dictionary<string, string>();
            list["module:sample?op=18&type=1"] = "Caixa de Diálogo";
            list["module:sample?op=18&type=2"] = "Caixa de Diálogo Modal";
            list["module:sample?op=18&type=3"] = "Caixa de Diálogo Modal com botão de fechar";
            list["module:sample?op=18&type=4"] = "Caixa de Diálogo Modal com botões de confirmação e abrir personalizado";
            list["module:sample?op=18&type=5"] = "Box de Informação com auto hide";
            list["module:sample?op=18&type=6"] = "Box de Alerta";

            XmlListCollection listElement = new XmlListCollection(XmlListType.UnorderedList, "Opções");
            foreach (KeyValuePair<string, string> kvp in list)
            {
                XmlAnchorCollection href = new XmlAnchorCollection(kvp.Key);
                href.addXmlnukeObject(new XmlnukeText(kvp.Value));
                listElement.addXmlnukeObject(href);
            }
            block.addXmlnukeObject(listElement);

            xmlnukeDoc.addXmlnukeObject(block);
        }

        protected void Opcao19(XmlnukeDocument xmlnukeDoc)
        {
            XmlBlockCollection block = new XmlBlockCollection("Exemplo 19: Media Gallery", BlockPosition.Center);


            XmlParagraphCollection para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Galeria de Imagens (album de Fotos)"));

            XmlnukeMediaGallery gallery = new XmlnukeMediaGallery(this._context, "Galeria1");
            gallery.addImage("common/imgs/albumsample/1.jpg", "common/imgs/albumsample/t_1.jpg", "Titulo Imagem 1", "Você pode colocar um caption aqui", 60, 60);
            gallery.addImage("common/imgs/albumsample/2.jpg", "common/imgs/albumsample/t_2.jpg", "Titulo Imagem 2", "Você pode colocar um caption aqui", 60, 60);
            gallery.addImage("common/imgs/albumsample/3.jpg", "common/imgs/albumsample/t_3.jpg", "Titulo Imagem 3", "Você pode colocar um caption aqui", 60, 60);
            para.addXmlnukeObject(gallery);
            block.addXmlnukeObject(para);

            para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("Flash, Youtube e Quicktime"));

            gallery = new XmlnukeMediaGallery(this._context);
            gallery.addEmbed("http://www.adobe.com/products/flashplayer/include/marquee/design.swf", 792, 294, "http://images.apple.com/trailers/wb/images/terminatorsalvation_200903131052.jpg", "Titulo Flash", "Aqui vc está vendo um Flash");
            gallery.addEmbed("http://movies.apple.com/movies/wb/terminatorsalvation/terminatorsalvation-tlr3_h.480.mov", 480, 204, "http://images.apple.com/trailers/wb/images/terminatorsalvation_200903131052.jpg", "Titulo Quicktime", "Aqui vc está vendo um Quicktime Movie");
            gallery.addEmbed("http://www.youtube.com/watch?v=4m48GqaOz90", 0, 0, "http://i1.ytimg.com/vi/4m48GqaOz90/default.jpg", "Titulo Youtube", "Aqui vc está vendo um Vídeo do Youtube");
            para.addXmlnukeObject(gallery);
            block.addXmlnukeObject(para);

            para = new XmlParagraphCollection();
            para.addXmlnukeObject(new XmlnukeText("IFrame"));

            gallery = new XmlnukeMediaGallery(this._context);
            gallery.addIFrame("module:sample", 480, 204, "", "IFrame");
            para.addXmlnukeObject(gallery);
            block.addXmlnukeObject(para);

            gallery = new XmlnukeMediaGallery(this._context, "Galeria2");
            gallery.setApi(true);
            gallery.setVisible(false);
            gallery.addImage("common/imgs/albumsample/4.jpg", "", "Titulo Imagem 1", "Você pode colocar um caption aqui");
            gallery.addImage("common/imgs/albumsample/5.jpg", "", "Titulo Imagem 2", "Você pode colocar um caption aqui");
            gallery.addImage("common/imgs/albumsample/1.jpg", "", "Titulo Imagem 3", "Você pode colocar um caption aqui");
            block.addXmlnukeObject(gallery);

            XmlFormCollection form = new XmlFormCollection(this._context, "", "Abrir por JavaScript");
            XmlInputButtons button = new XmlInputButtons();
            button.addButton("Clique para abrir a Galeria", "kk", "open_Galeria2()");
            form.addXmlnukeObject(button);
            block.addXmlnukeObject(form);

            xmlnukeDoc.addXmlnukeObject(block);
        }

    }

}

