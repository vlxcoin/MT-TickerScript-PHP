# MyToken ticker script[PHP]

## Requirements

#### PHP 5 >= 5.3.0, PHP 7(PHP Command Line)

## Coding

- Step 1: Generate MyToken API key(mt_api_key) and Initialize the configuration file
  - You need to generate a MyToken API key according [your docking exchange](#exchange-information)(* is the required field)

  - Use the exchange information to run the following code:

```
$ php script/GenerateMtApikey.php --name="yourexhcange" --website="https://www.yourexchange.io" --contact="your contact" --description="your exchange description" --logo_url="your logo url"
```
  - You can see the configuration from 'conf/app.ini', the section key is the lower case of exchange name, the 'mt_api_key' value is the MyToken APP key.

- Step 2: Coding APIs for a specific exchange
  - You can refer to the following directory "src/ThirdParty/GOPAX"

- Step 3: Coding scripts for a specific exchange
  - You can refer to the following directory "script/GOPAX"

- Step 4: Commit your code
  - Submit(commit && push) code based on your branch
  - Create a Pull Request from your branch to branch "dev", you must leave a commit about [your exchange information](#exchange-information)
  
<a name="exchange-information"></a><a name="2.1"></a>  
### Your Exchange Information
```
name(*): yourexhcange
website(*): https://www.yourexchange.io
contact(*): 8615266666666
description: this is description
logo_url: http://p1nzzscwm.bkt.clouddn.com/bittrex_logo.png
```  