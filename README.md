UserTakeOver
============
The UserTakeOver-Plugin gives you the possibility to take the view of another user.

![001][overview]
![002][leave]

Please read the Documentation on [Documentation.docx](/doc/Documentation.pdf?raw=true)

##Installation
###Router
The Subscription-Plugin needs a Router-Service to work. Please install the Service first:
 
You start in your ILIAS root directory

```bash
cd Services  
git clone https://github.com/studer-raimann/RouterService.git Router  
```
Switch to the setup-Menu of your Installation and perform a Structure-reload in the Tab Tools. this can take a few moments. After the reload has been performed, you can install the plugin.

###Plugin
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/  
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/  
git clone https://github.com/studer-raimann/UserTakeOver.git  
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.
This Plugin requires ActiveRecord and ilRouterGUI.

###Contact
studer + raimann ag  
Waldeggstrasse 72  
3097 Liebefeld  
Switzerland 

info@studer-raimann.ch  
www.studer-raimann.ch  


[overview]: /doc/Screenshots/001.png?raw=true "Button in Administraion"
[leave]: /doc/Screenshots/002.png?raw=true "Leave the User View"
