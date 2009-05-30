using System;
using System.Collections.Generic;
using System.Text;

// XMLNuke using
using com.xmlnuke.admin;
using com.xmlnuke.anydataset;
using com.xmlnuke.classes;
using com.xmlnuke.database;
using com.xmlnuke.engine;
using com.xmlnuke.exceptions;
using com.xmlnuke.international;
using com.xmlnuke.module;
using com.xmlnuke.processor;
using com.xmlnuke.util;
using System.Collections.Specialized;

namespace xmlnuke.sample
{
    public class Modulo : BaseModule
    {
        #region Basic Implementation

		protected string _teste;
		public string Teste
		{
			get { return this._teste; }
			set { this._teste = value; }
		}

        public override IXmlnukeDocument CreatePage()
        {
            this.defaultXmlnukeDocument = new XmlnukeDocument("Xmlnuke Module", "Just an abstract information");

            XmlBlockCollection block = new XmlBlockCollection("Block Sample", BlockPosition.Center);

			Debug.Print(this._context.joinUrlBase("/joao/teste/aaaa.aspx"));
			Debug.Print(this._context.joinUrlBase("teste/aaaa.aspx"));

			this._context.Debug();
			
			/*
			DBDataSet ds = new DBDataSet("testesqlite", this._context);
			ds.execSQL("insert into teste values (1, 'joao')");
			
			DbParameters param = new DbParameters();
			param.Add("chave", 2);
			param.Add("nome", "jajaja");
			ds.execSQL("insert into teste values ([[chave]], [[nome]])", param);
			
			IIterator it = ds.getIterator("select * from teste");
			Debug.Print(it);
			
			DbParameters param2 = new DbParameters();
			param2.Add("chave", 1);
			IIterator it2 = ds.getIterator("select * from teste where chave = [[chave]]", param2);
			Debug.Print(it2);
			*/

			/*
			ImageUtil im = new ImageUtil(@"c:\temp\original.jpg");
			im.resize(300, 500).Save(@"c:\temp\or300x500.gif");

			im = new ImageUtil(@"c:\temp\original.jpg");
			im.resize(500).Save(@"c:\temp\or500.jpg");

			im = new ImageUtil(@"c:\temp\original.jpg");
			im.cropImage(new System.Drawing.Rectangle(100, 100, 400, 400)).Save(@"c:\temp\orCroped.png");

			im = new ImageUtil(@"c:\temp\original.jpg");
			ImageUtil im2 = new ImageUtil(@"c:\temp\ubuntu.gif");
			im.stampImage(im2.GetImage(), StampPosition.Bottom).Save(@"c:\temp\orStamp0.png");
			im.stampImage(im2.GetImage(), StampPosition.BottomLeft).Save(@"c:\temp\orStamp1.jpg");
			im.stampImage(im2.GetImage(), StampPosition.BottomRight).Save(@"c:\temp\orStamp2.jpg");
			im.stampImage(im2.GetImage(), StampPosition.Center).Save(@"c:\temp\orStamp3.jpg");
			im.stampImage(im2.GetImage(), StampPosition.Left).Save(@"c:\temp\orStamp4.jpg");
			im.stampImage(im2.GetImage(), StampPosition.Right).Save(@"c:\temp\orStamp5.jpg");
			im.stampImage(im2.GetImage(), StampPosition.Top).Save(@"c:\temp\orStamp6.jpg");
			im.stampImage(im2.GetImage(), StampPosition.TopLeft).Save(@"c:\temp\orStamp7.jpg");
			im.stampImage(im2.GetImage(), StampPosition.TopRight).Save(@"c:\temp\orStamp8.jpg");
			im.Save(@"c:\temp\orStamp.jpg");

			im = new ImageUtil(@"c:\temp\original.jpg");
			im2 = new ImageUtil(@"c:\temp\ubuntu.gif");
			im.stampImageAndText(im2.GetImage(), StampPosition.TopRight, "Isso é um teste").Save(@"c:\temp\orStamp.png");
			*/

			ProcessPageFields fields = new ProcessPageFields();
			
			ProcessPageField field = ProcessPageFields.Factory("chave", "Campo Chave", 5, true, true);
			field.key = true;
			field.dataType = INPUTTYPE.NUMBER;
			fields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("nome", "Campo Nome", 20, true, true);
			fields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("data", "Campo Data", 10, true, true);
			field.dataType = INPUTTYPE.DATE;
			fields.addProcessPageField(field);

			field = ProcessPageFields.Factory("dual", "Campo Dual", 30, true, true);
			NameValueCollection array = new NameValueCollection();
			array["Val1"] = "Esse é o valor 1";
			array["Val2"] = "Esse é o valor 2";
			array["Val3"] = "Esse é o valor 3";
			array["Val4"] = "Esse é o valor 4";
			array["Val5"] = "Esse é o valor 5";
			field.arraySelectList = array;
			field.fieldXmlInput = XmlInputObjectType.DUALLIST;
			fields.addProcessPageField(field);

			//ProcessPageStateDB processpage = 
			//	new ProcessPageStateDB(this._context, fields, "Aqui", "module:xmlnuke.sample.modulo", null, "teste", "mytest");

			AnydatasetFilenameProcessor anydataFile = new AnydatasetFilenameProcessor("testeproc", this._context);
			ProcessPageStateAnydata processpage = new ProcessPageStateAnydata(this._context, fields, "Aqui", "module:xmlnuke.sample.modulo", null, anydataFile);

			block.addXmlnukeObject(processpage);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);
			
			/*
            XmlParagraphCollection p = new XmlParagraphCollection();
            p.addXmlnukeObject(new XmlnukeText("Sample text"));
            block.addXmlnukeObject(p);

            this.defaultXmlnukeDocument.addXmlnukeObject(block);

			this.bindParameteres();
			this.processEvent();


			XmlFormCollection form = new XmlFormCollection(this._context, "module:xmlnuke.sample.Modulo", "Form Exemplo");
			XmlInputButtons button = new XmlInputButtons();
			button.addClickEvent("Teste Evento", "TesteMetodo");
			button.addClickEvent("Teste Evento 2", "TesteMetodo2");
			form.addXmlnukeObject(button);
			block.addXmlnukeObject(form);

			p = new XmlParagraphCollection();
			XmlnukePoll poll = new XmlnukePoll(this._context, "module:xmlnuke.sample.modulo", "TESTE", "pt-br");
			poll.processVote();
			p.addXmlnukeObject(poll);
			block.addXmlnukeObject(p);
			
			AnydatasetFilenameProcessor file = new AnydatasetFilenameProcessor("guestbook", this._context);
            AnyDataSet any = new AnyDataSet(file);

            XmlEditList xmledit1 = new XmlEditList(this._context, "Original Anydata", "");
            xmledit1.setReadOnly(true);
            xmledit1.setDataSource(any.getIterator());
            p.addXmlnukeObject(xmledit1);
            
            SortCompareDate sc = new SortCompareDate();
            sc.DateFormat = DATEFORMAT.YMD;
            any.Sort("date", sc);
            XmlEditList xmledit2 = new XmlEditList(this._context, "Sorted by Date", "");
            xmledit2.setReadOnly(true);
            xmledit2.setDataSource(any.getIterator());
            p.addXmlnukeObject(xmledit2);

            any.Sort("frommail", new SortCompareString());
            XmlEditList xmledit3 = new XmlEditList(this._context, "Sorted by email", "");
            xmledit3.setReadOnly(true);
            xmledit3.setDataSource(any.getIterator());
            p.addXmlnukeObject(xmledit3);

            // Demonstrate the use o TextFileDataSet
            FileUtil.QuickFileWrite("test.txt", "Joao Gilberto,Developer,Brazil,10,13\nJohn Doe,All Hands Person,Unknow,15,7");
		
		    TextFileDataSet fileds = new TextFileDataSet(this._context, "test.txt", new string[]{"Name", "Function", "Profile", "Qty", "InStock"});
		    IIterator it = fileds.getIterator();
		    //Debug.Print(it);
		    //this._context.Debug();
		
		    XmlEditList editList = new XmlEditList(this._context, "Text File Dataset", "");
		    editList.setDataSource(fileds.getIterator());
		    p.addXmlnukeObject(editList);

			fileds = new TextFileDataSet(this._context, "test.txt", new string[] { "Name", "Function", "Profile", "Qty", "InStock" });
			it = fileds.getIterator();
			XmlChart chart = new XmlChart(this._context, "Titulo", it, ChartOutput.Flash, ChartSeriesFormat.Column);
			chart.setLegend("Name", "#000000", "#C0C0C0");
			chart.addSeries("Qty", "Quantity", "#000000");
			chart.addSeries("InStock", "In Stock", "#000000");
			p.addXmlnukeObject(chart);
			
			*/

            return this.defaultXmlnukeDocument;
        }

        public override LanguageCollection WordCollection()
        {
            return base.WordCollection();
        }

        public override void Setup(XMLFilenameProcessor xmlModuleName, Context context, object customArgs)
        {
            base.Setup(xmlModuleName, context, customArgs);
        }

        #endregion

        #region Security

        public override bool requiresAuthentication()
        {
            return base.requiresAuthentication();
        }

        public override AccessLevel getAccessLevel()
        {
            return base.getAccessLevel();
        }

        public override string[] getRole()
        {
            return base.getRole();
        }

        public override bool accessGranted()
        {
            return base.accessGranted();
        }

        public override void processInsufficientPrivilege()
        {
            base.processInsufficientPrivilege();
        }

        #endregion

        #region Cache
        public override bool useCache()
        {
            return base.useCache();
        }

        #endregion

        #region Advanced Cache Methods
        public override string getFromCache()
        {
            return base.getFromCache();
        }

        public override bool hasInCache()
        {
            return base.hasInCache();
        }

        public override void resetCache()
        {
            base.resetCache();
        }

        public override void saveToCache(string content)
        {
            base.saveToCache(content);
        }

        #endregion

        #region Advanced Setup Options
        public override void CustomSetup(object customArg)
        {
            base.CustomSetup(customArg);
        }
        #endregion
		public void TesteMetodo_Event()
		{
			Debug.Print("Event Fired");
		}

    }
}
