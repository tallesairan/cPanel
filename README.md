# cPanel UAPI + APIv2 PHP Wrapper

The `cPanel` class is a PHP library designed to interact with cPanel APIs, a popular web hosting management platform. This class allows developers to execute various functions of the cPanel UAPI and APIv2 directly from their PHP applications. With the ability to use a proxy and an advanced system of cookies and logs, this class offers enhanced flexibility and security for developers.

This class can help improve productivity in several ways:

1.  Task Automation: The class enables developers to automate cPanel tasks, such as managing email accounts, domains, and databases, directly from their PHP applications.

2.  Application Integration: The class makes it easy to integrate cPanel with custom applications, allowing developers to manage cPanel resources directly from their systems.

3.  Proxy Management: The ability to use a proxy and an advanced system of cookies and logs allows developers to access cPanel from multiple locations and IPs, improving security and flexibility.

4.  Programmatic Control: The class provides a programmatic way to interact with cPanel APIs, allowing developers to create customized and efficient solutions for managing web hosting.

By utilizing the `cPanel` class, developers can streamline their hosting management operations and improve integration between cPanel and their custom applications, increasing productivity and simplifying the workflow.


## Installation via composer
Add it to your project via composer using: 
`composer require tallesairan/cpanel`


## Examples 
* Simple Login and List files without proxy

````
<?php
use AiranDev\cPanel;

$ips = [];
$useProxy = false;
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    $allFiles = ($uapi->execute('uapi', 'Fileman', 'list_files',[
            'dirs' => '/public_html',
            'dir' => '/public_html',
            'path' => '/public_html',
            'types'=>'file'
    ]));
    var_dump($allFiles);
        
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }
?>

````

* Simple Login and list_files with Proxy:


````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    $allFiles = ($uapi->execute('uapi', 'Fileman', 'list_files',[
            'dirs' => '/public_html',
            'dir' => '/public_html',
            'path' => '/public_html',
            'types'=>'file'
    ]));
    var_dump($allFiles);
        
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }
?>

````
* Simple Login and File Upload


````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

   $file1 = new CURLFile($indexPath,'text/plain','index.php');
       
    $fileUploadResponse = $uapi->execute('uapi', 'Fileman', 'upload_files', [
            'dir' => 'public_html',
            'overwrite'=>'1',
            'file' => $file1
        ]
    );
    var_dump($fileUploadResponse);
        
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }



````
* Simple Login and File Extract


````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

   $fileExtractResponse = $uapi->execute('api2','Fileman','fileop',[
        'op'                => 'extract',
        'sourcefiles'       => 'example.zip',
        'destfiles'         => '/public_html',
        'doubledecode'      => '1',
        'overwrite'=>'1' // overwrite if exists
    ]);
    // multiples files extract
    $fileExtractResponse = $uapi->execute('api2','Fileman','fileop',[
        'op'                => 'extract',
        'sourcefiles'       => 'example.zip,example2.zip',
        'destfiles'         => '/public_html,/dir2',
        'doubledecode'      => '1',
        'overwrite'=>'1' // overwrite if exists
    ]);
    var_dump($fileUploadResponse);
        
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }



````
* Simple Login and new ftp account 

````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    $api2Request = $uapi->execute('uapi','Ftp','add_ftp',[
            'user'=>'user@domain.com',
            'pass'=>'Password@1',
            'homedir'=>'/'
    ]); 
        
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }


````

* Create sub domain

````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    // List Domains
    $result = ($uapi->execute('uapi', 'DomainInfo', 'list_domains'));
    // Create Sub-Domain
    $parameters = [
        'domain'                => "demo",
        'rootdomain'            => "example.com",
        'dir'                   => "/home/username/public_html/sub/demo",
        'disallowdot'           => '1',
    ];
    $result = $uapi->execute('api2', 'SubDomain', 'addsubdomain', $parameters);    
   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }
````

* Manage Email

````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    // List Emails
    $result = ($uapi->execute('uapi', 'Email', 'list_pops'));
    // Add Email
    $parameters = [
            'email' => "demo",
            'password' => "pass01",
            'domain' =>  "demo.com",
            'quota' => "120" //in MB, 0 for unlimited
        ];

    $result = $uapi->execute('uapi', 'Email', 'add_pop', $parameters);

    // Delete Email
    $parameters = [
            'email'           => "demo", // email before @
            'domain'          => "demo.com",
        ];
    $result= ($uapi->execute('uapi', 'Email', 'delete_pop', $parameters));


   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }
````

* Manage Database

````
<?php
use AiranDev\cPanel;
$useProxy = true;
$ips = ["128.0.0.1:3309","8.8.8.8:3309","8.4.4.8:3309"];
try{
    $uapi = new cPanel( $user , $pass, $srv, 2083, true,$useProxy,$ips);

    // List Databases (Using API 2)
    $result = $uapi->execute('api2', 'MysqlFE', 'listdbs'); //Response structure is diff. from UAPI response
    // Create Database
    $parameters = [
            'name' => "prefix_database_name",
        ];
    $result = ($uapi->execute('uapi', 'Mysql', 'create_database', $parameters));
    // Delete Database
    $parameters = [
        'name' => "prefix_database_name",
    ];
    $result = ($uapi->execute('uapi', 'Mysql', 'delete_database', $parameters));


   }  catch(Exception $e){
         echo $e->getMessage();
         exit;
    }
````

----------
## Doubts ?
## Guide to Replacing cPanel API 1 Functions with UAPI Equivalents 

This document lists the UAPI functions which replace previously-deprecated cPanel API 1 functions. We introduced [UAPI](https://api.docs.cpanel.net/cpanel/introduction) in cPanel & WHM version 11.42. As we developed additional UAPI functions, we created equivalents to some [cPanel API 1](https://documentation.cpanel.net/display/DD/Guide+to+cPanel+API+1) functions. Beginning with cPanel & WHM version 82, we started development for new UAPI modules and functions. These new modules and functions replace any cPanel API 1 functions without a current equivalent.

We are [actively removing](https://docs.cpanel.net/knowledge-base/cpanel-product/cpanel-deprecation-plan) the cPanel API 1 functions beginning in cPanel & WHM version 88. We strongly recommend that anyone using a cPanel API 1 function use a UAPI function instead.

[](https://api.docs.cpanel.net/guides/guide-to-replacing-cpanel-api-1-functions-with-uapi-equivalents#replace-cpanel-api-1-functions-with-uapi-equivalents)
-----------------------------------------------------------------------------------------------------------------------------------------------------------

Replace cPanel API 1 functions with UAPI equivalents
----------------------------------------------------

To retrieve a list of cPanel API 1 functions that custom integrations call your system, call the following WHM API 1 functions:

-   [`get_api_calls`](https://api.docs.cpanel.net/openapi/whm/operation/get_api_calls/)
-   [`get_api_pages`](https://api.docs.cpanel.net/openapi/whm/operation/get_api_pages/)

The following examples display code from a cPanel API 1 function and its UAPI equivalent. Click the tab below that corresponds to your chosen development language.

Note:

The examples below compare the cPanel API 1 [SetLang::setlang](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+SetLang%3A%3Asetlang) function and the UAPI [Locale::set_locale](https://api.docs.cpanel.net/openapi/cpanel/operation/set_locale/) function.

#### [](https://api.docs.cpanel.net/guides/guide-to-replacing-cpanel-api-1-functions-with-uapi-equivalents#liveapi-php-class)LiveAPI PHP Class

cPanel API 1 function

```
$cpanel = new CPANEL(); // Connect to cPanel - only do this once.

// Set the "en" locale for the account.
$new_language = $cpanel->api1(
    'SetLang', 'setlang',
        array('en')
);
```

UAPI Function

```
$uapi = new cPanel( $user , $pass, $srv, 2083);
# Set the "en" locale for the account.
$setLocale = ($uapi->execute('uapi', 'Locale', 'set_locale',[
        'locale' => 'en',
]));

```
 
Note:

For more information, read our [Guide to the LiveAPI System](https://documentation.cpanel.net/display/DD/Guide+to+the+LiveAPI+System).

For more information about how to replace a cPanel API 1 function with a UAPI function, read our [Replace a cPanel API 1 Function With a UAPI Function](https://documentation.cpanel.net/display/DD/Tutorial+-+Replace+a+cPanel+API+1+Function+With+a+UAPI+Function) documentation.

[](https://api.docs.cpanel.net/guides/guide-to-replacing-cpanel-api-1-functions-with-uapi-equivalents#uapi-functions-and-legacy-equivalents)
---------------------------------------------------------------------------------------------------- 

UAPI functions and legacy equivalents
-------------------------------------

The following tables list the UAPI functions that we added starting in cPanel & WHM version 82. The tables also list their cPanel API 1 equivalent functions.

For a complete list of UAPI functions and cPanel API 1 functions, read our [Guide to UAPI](https://api.docs.cpanel.net/cpanel/introduction/) and [Guide to cPanel API 1](https://documentation.cpanel.net/display/DD/Guide+to+cPanel+API+1) documentation.


#### Fileman

| cPanel API 1 Functions | UAPI Functions |
| --- | --- |
| [`Fileman::restoredb`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Fileman%3A%3Arestoredb) | [`Backup::restore_databases`](https://api.docs.cpanel.net/openapi/cpanel/operation/restore_databases/) |
| [`Fileman::restorefiles`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Fileman%3A%3Arestorefiles) | [`Backup::restore_files`](https://api.docs.cpanel.net/openapi/cpanel/operation/restore_files/) |
| [`Fileman::restoreaf`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Fileman%3A%3Arestoreaf) |

-   [`Backup::restore_email_filters`](https://api.docs.cpanel.net/openapi/cpanel/operation/restore_email_filters/)
-   [`Backup::restore_email_forwarders`](https://api.docs.cpanel.net/openapi/cpanel/operation/restore_email_forwarders/)

 


 


#### MySQL®

| cPanel API 1 Functions | UAPI Functions |
| --- | --- |
|[`Mysql::listdbs`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Alistdbs) |  [`Mysql::listdbsopt`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Alistdbsopt) |
| [`Mysql::number_of_dbs`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Anumber_of_dbs) | [`Mysql::list_databases`](https://api.docs.cpanel.net/openapi/cpanel/operation/list_databases/)
| [`Mysql::routines`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Aroutines) |[`Mysql::list_routines`](https://api.docs.cpanel.net/openapi/cpanel/operation/list_routines/) |
[`Mysql::listusers`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Alistusers) | [`Mysql::listusersopt`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Alistusersopt)
| [`Mysql::number_of_users`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Anumber_of_users) | [`Mysql::list_users`](https://api.docs.cpanel.net/openapi/cpanel/operation/Mysql-list_users/) |
| [`Mysql::updateprivs`](https://documentation.cpanel.net/display/DD/cPanel+API+1+Functions+-+Mysql%3A%3Aupdateprivs) | `Mysql::update_privileges` |



### Example For using uapi from cpanel documentation
cPanel API 1 function

```
$cpanel = new CPANEL(); // Connect to cPanel - only do this once.

// Set the "en" locale for the account.
$set_locale = $cpanel->uapi(
    'Locale', 'set_locale',
    array(
        'locale' => 'en',
    )
);
```

UAPI Function

```
$uapi = new cPanel( $user , $pass, $srv, 2083);
# Set the "en" locale for the account.
$setLocale = ($uapi->execute('uapi', 'Locale', 'set_locale',[
        'locale' => 'en',
]));

```

For more read at  [Guide to Replacing cPanel API 1 Functions with UAPI Equivalents](https://api.docs.cpanel.net/guides/guide-to-replacing-cpanel-api-1-functions-with-uapi-equivalents#fileman)

UAPI Docs [UAPI Complete Docs](https://api.docs.cpanel.net/cpanel/introduction/)


----------
## API
 

## Description
The `cPanel` class provides an interface to interact with cPanel's UAPI and APIv2. It supports using proxies, and has an advanced system for handling cookies and logs.

### Class Properties

#### $host
- Type: `string`
- The cPanel host URL.

#### $port
- Type: `integer`
- The cPanel host port.

#### $username
- Type: `string`
- The cPanel account username.

#### $password
- Type: `string`
- The cPanel account password.

#### $log
- Type: `boolean`
- Indicates whether logging is enabled.

#### $cFile
- Type: `string`
- The cookie file path.

#### $curlfile
- Type: `string`
- The log file path.

#### $emailArray, $cpsess, $homepage, $exPage
- These private properties are not currently used in the class.

#### $ips
- Type: `array`
- An array of proxy IPs.

#### $useragent
- Type: `string`
- The user agent string to use for HTTP requests.

#### $current_ip
- Type: `string`
- The current IP address for the session.

#### $useProxy
- Type: `boolean`
- Indicates whether to use a proxy or not.

### Class Methods

#### __construct
- Parameters:
  - `string` $username
  - `string` $password
  - `string` $host
  - `integer` $port (default: 2083)
  - `boolean` $log (default: false)
  - `boolean` $useProxy (default: false)
  - `array` $proxies (default: [])
- Initializes the `cPanel` class with the provided parameters.

#### getRandomProxy
- Return: `string`
- Returns a random proxy from the list of proxies.

 #### Request
- Parameters:
  - `[type]` $url
  - `array` $params (default: [])
- Return: `mixed`
- Description: Handles an HTTP request to the cPanel server. If `$params` is set, it will be a POST request. This method is private and used internally within the class.

#### signIn
- Parameters: None
- Return: `void`
- Description: Starts a session at the cPanel server. This method is private and called during the initialization of the `cPanel` class. It sets the `$cpsess`, `$homepage`, and `$exPage` properties of the class based on the response from the cPanel server.

#### execute
- Parameters:
  - `string` $api
  - `string` $module
  - `string` $function
  - `array` $parameters (default: [])
- Return: `void`
- Description: Executes the specified function using the given API type (api2 or uapi), module, and function, with the provided parameters. Calls either the `api2` or `uapi` method based on the API type.

#### uapi
- Parameters:
  - `[type]` $module
  - `[type]` $function
  - `array` $parameters (default: [])
- Return: `void`
- Description: Executes a UAPI function with the given module, function, and parameters. If the function is 'upload_files', the files are sent as an array of parameters. Returns a JSON decoded response from the cPanel server.

#### api2
- Parameters:
  - `string` $module
  - `string` $function
  - `array` $parameters (default: [])
- Return: `string`
- Description: Executes an APIv2 function with the provided module, function, and parameters. Builds the APIv2 request URL and returns a JSON decoded response from the cPanel server.

#### slugify
- Parameters:
  - `string` $text
- Return: `void`
- Description: A simple function to convert a given string into a slug (a URL-friendly version of the string). The function replaces non-letter or non-digit characters with hyphens, transliterates the text to ASCII, removes unwanted characters, trims hyphens from the beginning and end of the string, and converts the text to lowercase. If the resulting slug is empty, the function returns 'n-a'.

--------------------
## How to contribute
- Create a fork, make changes and send a pull request.
- Raise a issue


## RoadMap

- getSessionUrl() => Returns the current logged in session url

### Based On 
Built from an abandoned project [myPHPnotes/Cpanel](https://github.com/myPHPnotes/cPanel)


### License
Licensed under Apache 2.0. You can check its details [here](https://choosealicense.com/licenses/apache-2.0/ "here").

