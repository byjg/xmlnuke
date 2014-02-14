XMLNuke
=======

[![Build Status](https://travis-ci.org/byjg/xmlnuke.png?branch=php53)](https://travis-ci.org/byjg/xmlnuke)

*The master branch requires PHP53 or higher and is full PSR-0 compliant by using namespaces. The branch 'php50' is the legacy XMLNuke version and is now deprecated.*


XMLNuke is a Web Development Framework focused on the Data. Programming in XMLNuke you'll never more worry about open and close PHP tags and manage *spaghetti code*. All of your code is fully based in objects and all code produces only data in XML or JSON, you choose. 

This is a page in XMLNuke:

    class Home extends BaseModule 
    {
        public function __construct()
        {}

        public function CreatePage() 
        {
            $this->defaultXmlnukeDocument = new XmlnukeDocument("Title", "Abstract");
            ...
            return $this->defaultXmlnukeDocument;
        }
    }


You can easily add some requirements to your page without have to care about how handle this. For example, you can define that your page requires authentication, will be cached or requires to be executed in a SSL context. See the example below:

    class Home extends BaseModule 
    {
        /**
         * requiresAutentication(), getAccessLevel() and getRole() handle the page security and access level
         */
        public functon requiresAutentication()
        {
            return true;
        }
        
        public function getAccessLevel()
        {
            return AccessLevel::OnlyRole;
        }
    
        public function getRole()
        {
            return new array("DIRECTOR", "MANAGER");
        }
        
        
        /**
         * useCache determines if the XMLNuke will store your page in a cache or not.
         * By default XMLNuke can store in the StaticArray, FileSystem, MemCached. 
         * You can configure your own cache strategy by implementing the interface 
         * ICacheStorage.
         */ 
        public function useCache()
        {
            if ($this->_action != "")
            {
                  return false;
            }
            else
            {
                  return true;
            }
        }

        /** 
         * Determines if your page requires SSL or Not
         */
        public function requiresSSL()
        {
            return SSLAccess::ForceSSL;
        }

    }
    
If you work with models using the classic getter and setter or property you can add it to your page and the XMLNuke will output. For example:

    class MyClass
    {
        protected $_name;
        public function getName() ...;
        public function setName($value) ...;
        
        protected $_age;
        public function getAge() ...;
        public function setAge($value) ...;
    }
    
    class Home extends BaseModule 
    {
        public function CreatePage() 
        {
            $this->defaultXmlnukeDocument = new XmlnukeDocument("Title", "Abstract");
            ...
            
            $myClass = new MyClass();
            $myClass->setName('Joao');
            $myClass->setAge(39);
            ...
            $this->defaultXmlnukeDocument->addXmlnukeObject($myClass);
            
            return $this->defaultXmlnukeDocument;
        }
    }

After that you can associate a Snippet XSL to handle this data and produces HTML or whatever you want to produce by the XSL transformation. You can optionally get the raw data in XML or JSON by calling through your web browser:

    http://youserver/xmlnuke.php?module=byjg.home&rawxml=true&spath=//myclass
    
    <xmlnuke xpath="//myclass">
        <myclass>
            <name>Joao</name>
            <age>39</age>
        </myclass>
    </xmlnuke>
    
or

    http://yourserver/xmlnuke.php?module=byjg.home&rawjson=true&xpath=//myclass;
    
    {
        "myclass": {
            "name": "Joao",
            "age": "39"
        }
    }

See the Wiki for more examples;


## Installing

XMLNuke was developed using PHP 5.3 but it was tested with PHP 5.x or higher. 

### Web Install

You can install the XMLNuke by using the XMLNuke PHP5 Installer. It is a interactive interface and will guide you during all install process. This tool check if your system meets the XMLNuke requirements, download the XMLNuke and creates a project for you. *It is in beta stage*

See more at:   
https://github.com/byjg/xmlnuke-php5-installer



### Command Line (Debian/Ubuntu)

You have to install your web server (Apache2, Lighttd, nginx, ...). XMLNuke requires for PHP:

    apt-get install php5-xsl
    
Download the XMLNuke package. You can download from:
- the Zip package (https://github.com/byjg/xmlnuke/archive/master.zip) or 
- from repository by using the Git or SVN. 

Extract the package in any folder, e.g. /opt/xmlnuke.   
**Remember**: The XMLNuke folder cannot to be accessible from you Web Browser. 

Run at your terminal:

    cd /opt/xmlnuke
    ./copy-dist-files.sh link yes

Choose and create a folder for your project. This folder must be accessible through your web broswer. 

    mkdir /var/www/my-project
    php /opt/xmlnuke/create-php5-project.php /var/www/my-project default myproject en-us pt-br
    ln -s /opt/xmlnuke/xmlnuke-common /var/www/my-project/common
    
Now, just test it:

    http://yourserver/my-project
    

### Windows

You have to install your web server (Xampp, Apache2, IIS, ...) and configure it to run PHP5 scripts. Make sure that the XSL extension is installed. 

Download the XMLNuke package. You can download from:
- the Zip package (https://github.com/byjg/xmlnuke/archive/master.zip) or 
- from repository by using the Git or SVN. 

Extract the package in any folder, e.g. D:\data\xmlnuke.   
**Remember**: The XMLNuke folder cannot to be accessible from you Web Browser. 

Using the Windows Explorer find your XMLNuke folder and double click in the file "copy-dist-files.vbs". Follow the instructions. 

Choose and create a folder for your project (e.g. c:\InetPub\wwwroot\my-project). This folder must be accessible through your web broswer. Using the Windows Explorer find your XMLNuke Folder and double click in the file "create-php5-project.vbs" and follow the instructions. 

Now, just test it:

    http://yourserver/my-project
    
