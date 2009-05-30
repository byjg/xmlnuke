<?php

class UserModule extends BaseModule
{
	/**
	*@desc Default constructor
	*/
	public function UserModule()
	{}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();
		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Requested Not Found");
			$myWords->addText("en-us", "MESSAGE", "The requested module {0}");
			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo solicitado não foi encontrado");
			$myWords->addText("pt-br", "MESSAGE", "O módulo solicitado {0}");
		}
		
		return $myWords;
	}

	/**
	 * Returns if use cache
	 *
	 * @return bool
	 */
	public function useCache() 
	{
		return false;
	}

	/**
	 * Output error message
	 *
	 * @return PageXml
	 */
	public function CreatePage() 
	{
		// Não é mais necessário essa opção!
		//PageXml px = base->CreatePage();
		$myWords = $this->WordCollection();		
		
		$xmlnukeDoc = new XmlnukeDocument("Módulo de testes!", "Esse módulo permite demonstrar das novas funcionalidades de programação do XMLNuke");
		
		$this->bindParameteres();
		$this->processEvent();
		
		$xmlnukeDoc->setMenuTitle("Opções desse módulo");
		
		$xmlnukeDoc->addMenuItem("http://www.xmlnuke.com", "Xmlnuke", "Site do Xmlnuke");
		
		$block = new XmlBlockCollection("Módulo de Demonstração das Novas Funcionalidades", BlockPosition::Center);
		$xmlnukeDoc->addXmlnukeObject($block);
					
		$p = new XmlParagraphCollection();
		
		$form = new XmlFormCollection($this->_context, "module:xmlnuke.usermodule", "Form Exemplo");
		$button = new XmlInputButtons();
		$button->addClickEvent("Teste Evento", "TesteMetodo");
		$button->addClickEvent("Teste Evento 2", "TesteMetodo2");
		$form->addXmlnukeObject(new XmlInputHidden("teste", "Value set automatically"));
		$form->addXmlnukeObject($button);
		$block->addXmlnukeObject($form);
			
		/*
		$p->addXmlnukeObject(new XmlnukeText("Esse módulo tem como objetivo apenas demonstrar as novas funcionalidades e classes do XMLNuke. Portanto, ao observar o código fonte, é possível ver como o programa roda."));
		$block->addXmlnukeObject($p);
		
		FileUtil::QuickFileWrite("test.txt", "Joao Gilberto,Developer,Brazil\nJohn Doe,All Hands Person,Unknow");
		
		$fileds = new TextFileDataSet($this->_context, 'test.txt', array("Name", "Function", "Profile"));
		$it = $fileds->getIterator();
		Debug::PrintValue($it);
		$this->_context->Debug();
		
		$editList = new XmlEditList($this->_context, "Text File Dataset", "");
		$editList->setDataSource($fileds->getIterator());
		$p->addXmlnukeObject($editList);
		*/
		
		//$poll = new XmlnukePoll($this->_context, "module:xmlnuke.usermodule", "TESTE");
		//$poll->processVote();
		//$block->addXmlnukeObject($poll);
		
		return $xmlnukeDoc->generatePage();

	}
	
	public function requiresAuthentication()
	{
		return false;
	}
	
	protected $_teste;
	public function setTeste($value)
	{
		$this->_teste = $value;
	}
	public function getTeste()
	{
		return $this->_teste;
	}
		
	public function TesteMetodo_Event()
	{
		Debug::PrintValue("Event fired");
	}
	
}

?>
