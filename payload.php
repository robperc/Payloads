<?php

// Base class containing functions for converting arrays of key=>value pairs
// for different payload types into strings of xml
class Config {
	
	// Populated with values in child classes
	// Used by getXML function
	
	protected $keyVal = array();
	
	function __construct() {
		$sharedKeyVal = array(
			"PayloadType" => NULL, // Specific to the type of payload. See docs
			"PayloadVersion" => NULL, // The version number of the individual payload. A profile can consist of payloads with different version numbers.
			"PayloadIdentifier" => NULL, // A reverse-DNS-style identifier for the specific payload. Usually the same identifier as the root-level PayloadIdentifier value with an additional component appended.
			"PayloadUUID" => NULL, // String. A globally unique identifier for the profile. The actual content is unimportant, but it must be globally unique.
			"PayloadDisplayName" => NULL, // Optional. A human-readable name for the profile. This value is displayed on the Detail screen.
			"PayloadDescription" => NULL, // Optional. A description of the profile, shown on the Detail screen for the profile.
			"PayloadOrganization" => NULL, // Optional. A human-readable string containing the name of the organization that provided the profile.
		);
		$this->keyVal = $sharedKeyVal;
	}
	  
	// Converts keyVal dictionary instance variable into string containing 
	// xml-wrapped key=>value pairs
	public function getXML() {
		$xml = "";
		foreach ($this->keyVal as $key => $val) {
			if (isset($val)) {
				$xml .= "<key>" . $key . "</key>\n";
				if ($key == "PayloadContent") {
					$xml .= "<array>\n" . implode($val) . "</array>\n";
				} else {
					$xml .= $this->wrapVal($val) . "\n";
				}
			}
		}
		return $xml;
	}
	
	// Returns input value wrapped in xml tags indicating the input type
	protected function wrapVal($val) {
		$valType = gettype($val);
		switch ($valType) {
		    case "boolean":
				return ($val) ? "<true/>" : "<false/>";
		    default:
				return "<" . $valType . ">" . (string) $val . "</" . $valType . ">";
		}
	}
	
	// Uses bash command uuidgen to generate a Universally Unique IDentifier (UUID), 
	// a 128-bit value guaranteed to be unique over both space and time, for use
	// as the value of PayloadUUID
	protected function gen_uuid() {
		return rtrim(shell_exec("uuidgen"));
	}
}

// Class containing the key=>value pairs of the parent config profile payload
// and functions for embedding child payload xml and returning parent PayloadIdentifier
// Child payloads (ie for Mail, Calendars, etc) are embedded in the PayloadContent
// array of xml strings.
class Payload extends Config{

	function __construct($org) {
		parent::__construct();
		// Top level profile payload property list keys
		$payloadKeyVal = array(
			"PayloadContent" => NULL, // Optional. Array of payload dictionaries. Not present if IsEncrypted is true.
			"PayloadExpirationDate" => NULL, // Optional. A date on which a profile is considered to have expired and can be updated over the air.
			"PayloadRemovalDisallowed" => false, // Optional. If present and set to true, the user cannot delete the profile.
			"PayloadScope" => NULL, // Optional. String. Determines if the profile should be installed for the System or the User, User as the default value.
			"RemovalDate" => NULL, // Optional. date. The date on which the profile will be automatically removed.
			"DurationUntilRemoval" => NULL, // Optional. float. Number of seconds until the profile is automatically removed.
			"ConsentText" => NULL, // Localization key for language. See docs. Defaults to 'en' 
		);
	    $uuid = (string) $this->gen_uuid();
		$identifier = gethostname() . "." . (string) $uuid;
		$this->keyVal = array_merge($this->keyVal, $payloadKeyVal);
		$this->keyVal["PayloadType"] = "Configuration";
		$this->keyVal["PayloadVersion"] = 1;
		$this->keyVal["PayloadIdentifier"] = $identifier;
		$this->keyVal["PayloadUUID"] = $uuid;
		$this->keyVal["PayloadDisplayName"] = $org . " Config";
		$this->keyVal["PayloadDescription"] = $org . " Configuration Profile";
		$this->keyVal["PayloadOrganization"] = $org;
		$this->keyVal["PayloadContent"] = array();
	}
	
	// Wraps child payload xml string in correct tag and
	// pushes it to the PayloadContent array
	public function addPayloadContent($xml) {
		$xml = "<dict>\n" . $xml . "</dict>\n";
		array_push($this->keyVal["PayloadContent"], $xml);
	}
	
	// Returns the PayloadIdentifier value for the parent
	// config profile payload, needed for PayloadIdentifiers
	// of child payloads.
	public function getIdentifier() {
		return $this->keyVal["PayloadIdentifier"];
	}
}

// Class containing the key=>value pairs for Mail account child payloads
class Mail extends Config {
	
	function __construct($description, $name, $email, $username, $payloadIdentifier, $server_in, $server_out, $org) {
		parent::__construct();
		$mailKeyVal = array(
				"EmailAccountDescription" => NULL, # A user-visible description of the email account, shown in the Mail and Settings applications.
				"EmailAccountName" => NULL, # The full user name for the account. This is the user name in sent messages, etc.
				"EmailAccountType" => "EmailTypeIMAP", # Allowed values are EmailTypePOP and EmailTypeIMAP. Defines the protocol to be used for that account.
				"EmailAddress" => NULL, # Designates the full email address for the account. 
				"IncomingMailServerAuthentication" => "EmailAuthCRAMMD5", # Designates the authentication scheme for incoming mail.
				"IncomingMailServerHostName" => NULL, # Designates the incoming mail server host name (or IP address).
				"IncomingMailServerPortNumber" => 993, # Designates the incoming mail server port number. If no port number is specified, the default port for a given protocol is used.
				"IncomingMailServerUseSSL" => true, # Default true. Designates whether the incoming mail server uses SSL for authentication.
				"IncomingMailServerUsername" => NULL, # Designates the user name for the email account, usually the same as the email address up to the @ character.
				"IncomingPassword" => NULL, # Optional. Password for the Incoming Mail Server. Use only with encrypted profiles.
				"OutgoingPassword" => NULL, # Optional. Password for the Outgoing Mail Server. Use only with encrypted profiles.
				"OutgoingPasswordSameAsIncomingPassword" => true, # Optional. If set, the user will be prompted for pw only once and it will be used for both outgoing and incoming mail.
				"OutgoingMailServerAuthentication" => "EmailAuthCRAMMD5", # Designates the authentication scheme for outgoing mail.
				"OutgoingMailServerHostName" => NULL, # Designates the outgoing mail server host name (or IP address).
				"OutgoingMailServerPortNumber" => 587, # Optional. Designates the outgoing mail server port number.
				"OutgoingMailServerUseSSL" => true, # Default true. Designates whether the outgoing mail server uses SSL for authentication.
				"OutgoingMailServerUsername" => NULL, # Designates the user name for the email account, usually the same as the email address up to the @ character.
				"PreventMove" => NULL, # Default falseIf true, messages may not be moved out of this email account into another account.
				"PreventAppSheet" => false, # Default false. If true, this account is not available for sending mail in third-party applications.
				"SMIMEEnabled" => NULL, # Optional. Default false. If true, this account supports S/MIME.
				"SMIMESigningCertificateUUID" => NULL, # Optional. The PayloadUUID of the identity certificate used to sign messages sent from this account.
				"SMIMEEncryptionCertificateUUID" => NULL, # Optional. The PayloadUUID of the identity certificate used to decrypt messages coming into this account.
				"SMIMEEnablePerMessageSwitch" => false, # Optional. If set to true, enable the per-message signing and encryption switch. Defaults to false.
				"disableMailRecentsSyncing" => false, # If true, this account is excluded from address Recents syncing. This defaults to false.
		);
		$payloadType = "com.apple.mail.managed";
		$uuid = (string) $this->gen_uuid();
		$this->keyVal = array_merge($this->keyVal, $mailKeyVal);
		$this->keyVal["PayloadType"] = $payloadType;
		$this->keyVal["PayloadVersion"] = 1;
		$this->keyVal["PayloadIdentifier"] = $payloadIdentifier . "." . $payloadType . "." . $uuid;
		$this->keyVal["PayloadUUID"] = $uuid;
		$this->keyVal["PayloadDisplayName"] = $org . " Mail";
		$this->keyVal["PayloadDescription"] = "Configures e-mail settings";	
	    $this->keyVal["EmailAccountDescription"] = $description;
		$this->keyVal["EmailAccountName"] = $name;
		$this->keyVal["EmailAddress"] = $email;
		$this->keyVal["IncomingMailServerHostName"] = $server_in;
	    $this->keyVal["IncomingMailServerUsername"] = $username;
		$this->keyVal["OutgoingMailServerHostName"] = $server_out;
		$this->keyVal["OutgoingMailServerUsername"] = $username;
	}
}

// Not implemented yet
// Class containing the key=>value pairs for CalDav account child payloads
class CalDAV extends Config {
	function __construct($description, $hostname, $username, $payloadIdentifier) {
		parent::__construct();
		$calDAVKeyVal = array(
			"CalDAVAccountDescription" => NULL, # The description of the account.
			"CalDAVHostName" => NULL, # The server address. In OS X, this key is required.
			"CalDAVUsername" => NULL, # The user's login name. In OS X, this key is required.
			"CalDAVPassword" => NULL, # Optional. The user's password. Use only with encrypted profiles.
			"CalDAVUseSSL" => true, # Whether or not to use SSL.
			"CalDAVPort" => NULL, # Optional. The port on which to connect to the server. Defaults to Auto
			"CalDAVPrincipalURL" => NULL, # The base URL to the user’s calendar. In OS X this URL is required if the user doesn’t provide a password
		);
		$payloadType = "com.apple.caldav.account";
		$uuid = (string) $this->gen_uuid();
		$this->keyVal = array_merge($this->keyVal, $mailKeyVal);
		$this->keyVal["PayloadType"] = $payloadType;
		$this->keyVal["PayloadVersion"] = 1;
		$this->keyVal["PayloadIdentifier"] = $payloadIdentifier . "." . $payloadType . "." . $uuid;
		$this->keyVal["PayloadUUID"] = $uuid;
		$this->keyVal["PayloadDisplayName"] = $org . " Cal";
		$this->keyVal["PayloadDescription"] = "Configures Calendars";	
		$this->keyVal["CalDAVAccountDescription"] = $description;
		$this->keyVal["CalDAVHostName"] = $hostname;
		$this->keyVal["CalDAVUsername"] = $username;
	}
}

?>
