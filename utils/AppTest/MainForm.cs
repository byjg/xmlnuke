// project created on 24/11/2003 at 19:44
using System;
using System.Windows.Forms;
using System.Collections;
using System.Xml;

namespace com.xmlnuke.db
{
	class MainForm : System.Windows.Forms.Form
	{
		private System.Windows.Forms.CheckBox chkIncludeAll;
		private System.Windows.Forms.GroupBox groupBox;
		private System.Windows.Forms.TextBox txtPesquisa;
		private System.Windows.Forms.TextBox txtDoc;
		private System.Windows.Forms.TextBox txtRepositorio;
		private System.Windows.Forms.Button btnImportDocs;
		private System.Windows.Forms.Button btnRecriar;
		private System.Windows.Forms.Button btnRepositorio;
		private System.Windows.Forms.Button btnSalvarDoc;
		private System.Windows.Forms.Button btnPesquisa;
		private System.Windows.Forms.TextBox txtLang;
		private System.Windows.Forms.TextBox txtDir;
		private System.Windows.Forms.Button btnLer;
		private System.Windows.Forms.Button btnSalvar;
		private System.Windows.Forms.ListBox lstResultado;
		private System.Windows.Forms.Label lblTempo;
		private System.Windows.Forms.TextBox txtXML;
		private System.Windows.Forms.Button btnLerDoc;


		public XmlNukeDB repositorio = null;
		public DateTime horaRegistro;

		public MainForm()
		{
			InitializeComponent();
		}
	
		// THIS METHOD IS MAINTAINED BY THE FORM DESIGNER
		// DO NOT EDIT IT MANUALLY! YOUR CHANGES ARE LIKELY TO BE LOST
		void InitializeComponent() {
			this.btnLerDoc = new System.Windows.Forms.Button();
			this.txtXML = new System.Windows.Forms.TextBox();
			this.lblTempo = new System.Windows.Forms.Label();
			this.lstResultado = new System.Windows.Forms.ListBox();
			this.btnSalvar = new System.Windows.Forms.Button();
			this.btnLer = new System.Windows.Forms.Button();
			this.txtDir = new System.Windows.Forms.TextBox();
			this.txtLang = new System.Windows.Forms.TextBox();
			this.btnPesquisa = new System.Windows.Forms.Button();
			this.btnSalvarDoc = new System.Windows.Forms.Button();
			this.btnRepositorio = new System.Windows.Forms.Button();
			this.btnRecriar = new System.Windows.Forms.Button();
			this.btnImportDocs = new System.Windows.Forms.Button();
			this.txtRepositorio = new System.Windows.Forms.TextBox();
			this.txtDoc = new System.Windows.Forms.TextBox();
			this.txtPesquisa = new System.Windows.Forms.TextBox();
			this.groupBox = new System.Windows.Forms.GroupBox();
			this.chkIncludeAll = new System.Windows.Forms.CheckBox();
			this.groupBox.SuspendLayout();
			this.SuspendLayout();
			// 
			// btnLerDoc
			// 
			this.btnLerDoc.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)));
			this.btnLerDoc.Location = new System.Drawing.Point(168, 240);
			this.btnLerDoc.Name = "btnLerDoc";
			this.btnLerDoc.Size = new System.Drawing.Size(136, 23);
			this.btnLerDoc.TabIndex = 2;
			this.btnLerDoc.Text = "Carregar Documento";
			this.btnLerDoc.Click += new System.EventHandler(this.BtnLerDocClick);
			// 
			// txtXML
			// 
			this.txtXML.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom) 
						| System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.txtXML.Location = new System.Drawing.Point(16, 72);
			this.txtXML.Multiline = true;
			this.txtXML.Name = "txtXML";
			this.txtXML.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
			this.txtXML.Size = new System.Drawing.Size(432, 160);
			this.txtXML.TabIndex = 4;
			this.txtXML.Text = "txtXML";
			// 
			// lblTempo
			// 
			this.lblTempo.Dock = System.Windows.Forms.DockStyle.Bottom;
			this.lblTempo.Font = new System.Drawing.Font("Tahoma", 11F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.World, ((System.Byte)(0)));
			this.lblTempo.Location = new System.Drawing.Point(0, 430);
			this.lblTempo.Name = "lblTempo";
			this.lblTempo.Size = new System.Drawing.Size(464, 23);
			this.lblTempo.TabIndex = 15;
			this.lblTempo.Text = "Programa de Demonstração";
			// 
			// lstResultado
			// 
			this.lstResultado.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom) 
						| System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.lstResultado.Location = new System.Drawing.Point(8, 60);
			this.lstResultado.Name = "lstResultado";
			this.lstResultado.Size = new System.Drawing.Size(432, 56);
			this.lstResultado.TabIndex = 1;
			this.lstResultado.SelectedIndexChanged += new System.EventHandler(this.LstResultadoSelectedIndexChanged);
			// 
			// btnSalvar
			// 
			this.btnSalvar.Anchor = System.Windows.Forms.AnchorStyles.Bottom;
			this.btnSalvar.Location = new System.Drawing.Point(20, 272);
			this.btnSalvar.Name = "btnSalvar";
			this.btnSalvar.Size = new System.Drawing.Size(136, 23);
			this.btnSalvar.TabIndex = 6;
			this.btnSalvar.Text = "Salvar BTree";
			this.btnSalvar.Click += new System.EventHandler(this.BtnSalvarClick);
			// 
			// btnLer
			// 
			this.btnLer.Anchor = System.Windows.Forms.AnchorStyles.Bottom;
			this.btnLer.Location = new System.Drawing.Point(164, 272);
			this.btnLer.Name = "btnLer";
			this.btnLer.Size = new System.Drawing.Size(136, 23);
			this.btnLer.TabIndex = 7;
			this.btnLer.Text = "Carregar BTree";
			this.btnLer.Click += new System.EventHandler(this.BtnLerClick);
			// 
			// txtDir
			// 
			this.txtDir.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.txtDir.Location = new System.Drawing.Point(160, 40);
			this.txtDir.Name = "txtDir";
			this.txtDir.Size = new System.Drawing.Size(288, 20);
			this.txtDir.TabIndex = 4;
			this.txtDir.Text = "..\\..\\xml";
			// 
			// txtLang
			// 
			this.txtLang.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.txtLang.Location = new System.Drawing.Point(400, 8);
			this.txtLang.Name = "txtLang";
			this.txtLang.Size = new System.Drawing.Size(48, 20);
			this.txtLang.TabIndex = 16;
			this.txtLang.Text = "pt-br";
			// 
			// btnPesquisa
			// 
			this.btnPesquisa.Location = new System.Drawing.Point(304, 24);
			this.btnPesquisa.Name = "btnPesquisa";
			this.btnPesquisa.TabIndex = 3;
			this.btnPesquisa.Text = "Pesquisar";
			this.btnPesquisa.Click += new System.EventHandler(this.BtnPesquisaClick);
			// 
			// btnSalvarDoc
			// 
			this.btnSalvarDoc.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)));
			this.btnSalvarDoc.Location = new System.Drawing.Point(16, 240);
			this.btnSalvarDoc.Name = "btnSalvarDoc";
			this.btnSalvarDoc.Size = new System.Drawing.Size(144, 23);
			this.btnSalvarDoc.TabIndex = 14;
			this.btnSalvarDoc.Text = "Salvar Documento";
			this.btnSalvarDoc.Click += new System.EventHandler(this.BtnSalvarDocClick);
			// 
			// btnRepositorio
			// 
			this.btnRepositorio.Location = new System.Drawing.Point(16, 8);
			this.btnRepositorio.Name = "btnRepositorio";
			this.btnRepositorio.Size = new System.Drawing.Size(136, 23);
			this.btnRepositorio.TabIndex = 10;
			this.btnRepositorio.Text = "Manipular Repositorio";
			this.btnRepositorio.Click += new System.EventHandler(this.BtnRepositorioClick);
			// 
			// btnRecriar
			// 
			this.btnRecriar.Anchor = System.Windows.Forms.AnchorStyles.Bottom;
			this.btnRecriar.Location = new System.Drawing.Point(308, 272);
			this.btnRecriar.Name = "btnRecriar";
			this.btnRecriar.Size = new System.Drawing.Size(136, 23);
			this.btnRecriar.TabIndex = 12;
			this.btnRecriar.Text = "Recriar BTree";
			this.btnRecriar.Click += new System.EventHandler(this.BtnRecriarClick);
			// 
			// btnImportDocs
			// 
			this.btnImportDocs.Location = new System.Drawing.Point(16, 40);
			this.btnImportDocs.Name = "btnImportDocs";
			this.btnImportDocs.Size = new System.Drawing.Size(136, 23);
			this.btnImportDocs.TabIndex = 2;
			this.btnImportDocs.Text = "Importar Documentos";
			this.btnImportDocs.Click += new System.EventHandler(this.btnImportDocsClick);
			// 
			// txtRepositorio
			// 
			this.txtRepositorio.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.txtRepositorio.Location = new System.Drawing.Point(160, 8);
			this.txtRepositorio.Name = "txtRepositorio";
			this.txtRepositorio.Size = new System.Drawing.Size(232, 20);
			this.txtRepositorio.TabIndex = 9;
			this.txtRepositorio.Text = "E:\\Temp\\Repositorio";
			// 
			// txtDoc
			// 
			this.txtDoc.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.txtDoc.Location = new System.Drawing.Point(312, 240);
			this.txtDoc.Name = "txtDoc";
			this.txtDoc.Size = new System.Drawing.Size(136, 20);
			this.txtDoc.TabIndex = 4;
			this.txtDoc.Text = "docxml";
			// 
			// txtPesquisa
			// 
			this.txtPesquisa.Location = new System.Drawing.Point(8, 24);
			this.txtPesquisa.Name = "txtPesquisa";
			this.txtPesquisa.Size = new System.Drawing.Size(152, 20);
			this.txtPesquisa.TabIndex = 0;
			this.txtPesquisa.Text = "";
			// 
			// groupBox
			// 
			this.groupBox.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left) 
						| System.Windows.Forms.AnchorStyles.Right)));
			this.groupBox.Controls.Add(this.btnPesquisa);
			this.groupBox.Controls.Add(this.chkIncludeAll);
			this.groupBox.Controls.Add(this.lstResultado);
			this.groupBox.Controls.Add(this.txtPesquisa);
			this.groupBox.Location = new System.Drawing.Point(8, 304);
			this.groupBox.Name = "groupBox";
			this.groupBox.Size = new System.Drawing.Size(448, 120);
			this.groupBox.TabIndex = 1;
			this.groupBox.TabStop = false;
			this.groupBox.Text = "Pesquisar Palavras na BTree";
			// 
			// chkIncludeAll
			// 
			this.chkIncludeAll.Location = new System.Drawing.Point(168, 24);
			this.chkIncludeAll.Name = "chkIncludeAll";
			this.chkIncludeAll.Size = new System.Drawing.Size(128, 24);
			this.chkIncludeAll.TabIndex = 2;
			this.chkIncludeAll.Text = "Qualquer Ocorrencia";
			// 
			// MainForm
			// 
			this.AutoScaleBaseSize = new System.Drawing.Size(5, 13);
			this.ClientSize = new System.Drawing.Size(464, 453);
			this.Controls.Add(this.txtLang);
			this.Controls.Add(this.lblTempo);
			this.Controls.Add(this.btnSalvarDoc);
			this.Controls.Add(this.txtXML);
			this.Controls.Add(this.btnRecriar);
			this.Controls.Add(this.btnRepositorio);
			this.Controls.Add(this.txtRepositorio);
			this.Controls.Add(this.btnLer);
			this.Controls.Add(this.btnSalvar);
			this.Controls.Add(this.txtDir);
			this.Controls.Add(this.btnImportDocs);
			this.Controls.Add(this.groupBox);
			this.Controls.Add(this.btnLerDoc);
			this.Controls.Add(this.txtDoc);
			this.MinimumSize = new System.Drawing.Size(472, 480);
			this.Name = "MainForm";
			this.Text = "This is my form";
			this.Load += new System.EventHandler(this.MainFormLoad);
			this.groupBox.ResumeLayout(false);
			this.ResumeLayout(false);
		}
			
		[STAThread]
		public static void Main(string[] args)
		{
			Application.Run(new MainForm());
		}


		void MainFormLoad(object sender, System.EventArgs e)
		{
			txtDir.Text = System.IO.Directory.GetCurrentDirectory();
		}
		
		void ContadorDeTempo(bool fim)
		{
			if (!fim)
			{
				lblTempo.Text = "Aguarde...";
				lblTempo.BackColor = System.Drawing.Color.Red;
				lblTempo.Refresh();
				//Cursor.Current = Cursors.WaitCursor;
				horaRegistro = DateTime.Now;
			}
			else
			{
				TimeSpan ts = DateTime.Now - horaRegistro;
				//Cursor.Current = Cursors.Default;
				lblTempo.BackColor = System.Drawing.Color.Silver;
				lblTempo.Text = "Total de Tempo decorrido: " + ts.TotalMilliseconds.ToString();
				//MessageBox.Show("Termino Processamento: " + ts.TotalMilliseconds.ToString());
			}
		}

		void BtnRepositorioClick(object sender, System.EventArgs e)
		{
			//string lang = System.Globalization.CultureInfo.CurrentCulture.TwoLetterISOLanguageName.ToString();
			
			this.ContadorDeTempo(false);
			repositorio = new XmlNukeDB(txtRepositorio.Text, txtLang.Text, true);
			this.ContadorDeTempo(true);
		}
		
		/*
		void ButtonClick(object sender, System.EventArgs e)
		{

	        mainBTree = null;
			
	    	mainBTree = BTreeUtil.insertTokens(
				  "When multiple pointer parts are provided, an XPointer processor must evaluate them in left-to-right order. If the XPointer processor does not support the scheme used in a pointer part, it skips that pointer part. If a pointer part does not identify any subresources, evaluation continues and the next pointer part, if any, is evaluated. The result of the first pointer part whose evaluation identifies one or more subresources is reported by the XPointer processor as the result of the pointer as a whole, and evaluation stops. If no pointer part identifies subresources, it is an error. " , "Documento Falso Numero 1", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "In the following example, if the 'xpointer' pointer part is not understood or fails to identify any subresources, the 'element' pointer part is evaluated. If the 'xpointer' pointer part identifies subresources, the 'element' pointer part is not evaluated." , "Documento Falso Numero 2", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "Software components claiming to be XPointer processors must conform to this XPointer Framework specification and any other specifications that, together with this specification, define the minimum conformance level for XPointer, and may conform to additional XPointer scheme specifications. XPointer processors must document the additional scheme specifications to which they conform. Specifications that depend on XPointer processing should document the schemes they require and support.", "Documento Falso Numero 3", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "XPointer processor behaviour depends on the availability of certain information from an XML resource: in the terms provided by the [Infoset], the information items and properties tabulated below may be relevant. The presence of some of these items and properties depends in turn on conformant DTD or XML Schema processing: conformant XPointer processors are not required to do such processing, but if they do, shorthand pointer processing will take advantage of the information thus provided (see 3.2 Shorthand Pointer).", "Documento Falso Numero 4", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "A software component that incorporates or uses an XPointer processor because it needs to access XML subresources. The occurrence and usage of XPointers, and the behavior to be applied to resources and subresources obtained by processing those XPointers, are governed by the definition of each application's corresponding data format (which could be XML-based or non-XML-based). For example, HTML [HTML] Web browsers and XInclude processors are applications that might use XPointer processors.	" , "Documento Falso Numero 5", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "This specification defines the XML Pointer Language (XPointer) Framework, an extensible system for XML addressing that underlies additional XPointer scheme specifications. The framework is intended to be used as a basis for fragment identifiers for any resource whose Internet media type is one of text/xml, application/xml, text/xml-external-parsed-entity, or application/xml-external-parsed-entity. Other XML-based media types are also encouraged to use this framework in defining their own fragment identifier languages.", "Documento Falso Numero 6", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
				  "Many types of XML-processing applications need to address into the internal structures of XML resources using URI references, for example, the XML Linking Language [XLink], XML Inclusions [XInclude], the Resource Description Framework [RDF], and SOAP 1.2 [SOAP12]. This specification does not constrain the types of applications that utilize URI references to XML resources, nor does it constrain or dictate the behavior of those applications once they locate the desired information in those resources.", "Documento Falso Numero 7", mainBTree);
	    	mainBTree = BTreeUtil.insertTokens(
	      		  "Isso é um teste de verdade e espero que dê certo, ok? Mas se mesmo assim não der certo não tem problema, não é?", "Documento Falso Numero 8", mainBTree);

		}
		*/
		
		void BtnPesquisaClick(object sender, System.EventArgs e)
		{
	    	lstResultado.Items.Clear();

			this.ContadorDeTempo(false);
	    	ArrayList arr = repositorio.searchDocuments(txtPesquisa.Text, chkIncludeAll.Checked);
			this.ContadorDeTempo(true);
			
	    	if (arr == null)
	    	{
		    	MessageBox.Show("Nao localizado!");
	    	}
	    	else
	    	{
	    		foreach(string s in arr)
	    		{
		    		lstResultado.Items.Add( s );
	    		}
	    	}
		}
		
		void LstResultadoSelectedIndexChanged(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			txtDoc.Text = lstResultado.SelectedItem.ToString();
			XmlDocument xml = repositorio.getDocument(txtDoc.Text);
			txtXML.Text = xml.OuterXml;
			this.ContadorDeTempo(true);
			/*
			System.Diagnostics.Process myp = new System.Diagnostics.Process();
			myp.StartInfo.FileName = lstResultado.SelectedItem.ToString();
			myp.EnableRaisingEvents = true;
			myp.Start();
			*/
		}
		
		void BtnSalvarClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			repositorio.saveIndex();
			this.ContadorDeTempo(true);
		}
		
		void BtnLerClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			repositorio.loadIndex();
			this.ContadorDeTempo(true);
		}
		
		void btnImportDocsClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			repositorio.importDocuments(txtDir.Text);
			this.ContadorDeTempo(true);
		}
		
		void BtnSalvarDocClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			repositorio.saveDocument(txtDoc.Text, txtXML.Text);
			this.ContadorDeTempo(true);
		}
		
		void BtnLerDocClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			XmlDocument xml = repositorio.getDocument(txtDoc.Text);
			this.ContadorDeTempo(true);
			txtXML.Text = xml.OuterXml;
		}
		
		void BtnRecriarClick(object sender, System.EventArgs e)
		{
			this.ContadorDeTempo(false);
			repositorio.recreateIndex();
			this.ContadorDeTempo(true);
		}
		
	}			
}
