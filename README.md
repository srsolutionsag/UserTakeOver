UserTakeOver
============
The UserTakeOver-Plugin gives you the possibility to take the view of another user.

![001][overview]
![002][leave]

Please read the Documentation on [Documentation.docx](/doc/Documentation.pdf?raw=true)

##Requirements

In order to leave a user view the user you took over has to be member of a global role which has the administration permission “Search: User can use the global search in ILIAS”.  

If the user you took over isn’t in a global role who has the mentioned privilege you have to log out and log in again with a user who is allowed to use global search in order to reuse the UserTakeOver Plugin.

Therefore, we recommend giving the global user role the administration permission “Search: User can use the global search in ILIAS”. 

Go to Administration>Roles and click on User Title.

Afterwards you have to switch to the subtab “Administration Permissions”.

Under this subtab enable the checkbox “Search: User can use the global search in ILIAS”.

Finally click on the save button.

##Installation

Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/  
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/  
git clone https://github.com/studer-raimann/UserTakeOver.git  
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
support-core1@studer-raimann.ch
https://studer-raimann.ch  



[overview]: /doc/Screenshots/001.png?raw=true "Button in Administraion"
[leave]: /doc/Screenshots/002.png?raw=true "Leave the User View"
