# MyToken ticker script[PHP]

The connection between exchange open API and MyToken open API. 

## Requirements

#### PHP 5 >= 5.3.0, PHP 7(PHP Command Line)

#### Your exchange must have your own open API

## Layout
```
- autoload.php      // Autoload index file
+ conf
  | - app.ini       // Configure 
- examples      // call MyToken API test script
+ script/       // exchange timed script
  - GenerateMtApikey.php        // Generate MyToken API key script
+ src/
  - MT     // MyToken API and tool class(config, error_code, http)
  - ThirdParty     //ThirdParty exchange API
```

## Coding

- Step 1: Generate MyToken API key(mt_api_key) and Initialize the configuration file
  - You need to generate a MyToken API key according [your exchange information](#exchange-information)(* is the required field)

  - Use the exchange information to run the following code:
    ```
    $ php script/GenerateMtApikey.php --name="yourexhcange" --website="https://www.yourexchange.io" --contact="your contact" --description="your exchange description" --logo_url="your logo url"
    ```

  - You can see the configuration from 'conf/app.ini', the section key is the lower case of exchange name, the 'mt_api_key' value is the MyToken APP key.

- Step 2: Coding APIs for a specific exchange, within the directory of "src/ThirdParty/"
  - You can refer to the following directory "src/ThirdParty/GOPAX"

- Step 3: Coding scripts for a specific exchange, within the directory of "script/"
  - You can refer to the following directory "script/GOPAX"
  - You must code `TokenUpdateCommand.php`, `TickerUpdateCommand.php`
  - It's recommended to update the prices in batch, like `TickerBatchUpdateCommand.php`, because it's more efficient.

- Step 4: Commit your code
  - Submit(commit && push) code based on your branch
  - Create a Pull Request from your branch to branch "dev", you must leave a commit about [your exchange information](#exchange-information)
  
<a name="exchange-information"></a><a name="2.1"></a>  
### Your Exchange Information
```
name(*): veloxexchange
website(*): https://vlxexchange.com
contact(*): telegram: veloxcoin, email: support@vlxexchange.com
description: VeloxCoin [VLX] Exchange
logo_url: https://veloxproject.io/img/vlx.png
```  