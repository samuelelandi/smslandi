SMSLAND(I) is suite of apps which allows a  integration  between an
SMPP client (like Diafaan SMS) and Kannel + Asterisk Voip Server.
The SMPP client can send sms and receive confirmation of message delivery (DLR).

The original target was Centos 7.x and the file install.sh has been done for
this Linux distribution.

INSTALL:
Download this project in /usr/src/smslandi

REQUIREMENTS:
Install Asterisk Server
from bash shell launch this commands:

cd /usr/src/
./install.sh

USAGE:
"start.sh" is an example to start all the modules required (please customize
it as you wish)

In the folder /usr/src/smslandi/asterisk/etc/kannel there are the configuration files for Kannel,
well tested in a working machine, for most cases changes are required.

In the folder /usr/src/smslandi/assterisk/agi/ there is the programs called in the dialplan to
generate back the DLR: smslandidlr.php

in the folder /usr/src/smslandi/assterisk/etc/ you can find the configuration settings to be
inserted/added to your Asterisk (you must adapt the examples to your
dialplan)

In the folder  /usr/src/smslandi/src you can find the main module
smslandi.php that should run in background:
Adjust the variables at line 57 of smslandi.php:
	 // CUSTOMIZATION
        $ASTERISKSIPDESTINATION='103.51.3.219:5060';
        $ASTERISKSIPORIGIN='128.199.249.184:5060';
        $ASTERISKAMIIP='127.0.0.1';
        $ASTERISKAMIPORT='5038';
        $ASTERISKAMIUSER='amiuser';
        $ASTERISKAMIPWD='yoursecret';
        // END CUSTOMIZATION 


For any help, please drop a message to samuele@landi.ae



