<?php
//https://docs.moodle.org/dev/Data_manipulation_API
//PHP Fehler anzeigen
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1); 
//PHP Mailer Klasse laden
require_once 'PHPMailerAutoload.php';

class emails{
    public function sendEmails() {
        //globales Datenbankobjekt + Configobjekt "importieren"
        global $DB, $CFG;
        $xml = simplexml_load_file($CFG->dirroot ."/blocks/alarm/settings.xml");
        $days = (int)$xml->login;
        $seconds = $days * 24 * 60 * 60; //2592000 s = 30 d 
        $date = time();
        $text_pt1 = (string)$xml->text1_pt1;
        $text_pt2 = (string)$xml->text1_pt2;
        //Userobjekte von allen Usern die länger als $days Tage nicht in Moodle aktiv waren aus Datenbank einlesen (außer den Gast- und Adminuser)
        $userObjects = $DB->get_records_sql('SELECT * FROM {user} WHERE lastaccess < :lastaccess AND id != :guestuser AND id != :adminuser', 
                                            array('lastaccess' => $date-$seconds, 'guestuser' => 1, 'adminuser' => 2));
        
        //Userobjekte ausgeben (für Debugging)
//        print "<pre>";
//        print_r($userObjects);
//        print "</pre>";
        
        //Hostname und Port trennen (smtphostname:port)
        $smtpInfos = explode(":", $CFG->smtphosts);
        
        //Mailobjekt erstellen
        $mail = new PHPMailer;
        //Set mailer to use SMTP
        $mail->isSMTP();       
        //Specify main and backup SMTP servers
        $mail->Host = $smtpInfos[0];       
        //Enable SMTP authentication
        $mail->SMTPAuth = true;         
        //SMTP username
        $mail->Username = $CFG->smtpuser; 
        //SMTP password
        $mail->Password = $CFG->smtppass;      
        //Enable TLS encryption, `ssl` also accepted
        $mail->SMTPSecure = $CFG->smtpsecure;   
        //TCP port to connect to
        $mail->Port = $smtpInfos[1];                                    
        //Emailadresse Absender
        $mail->From = $CFG->noreplyaddress;
        //Absendername
        $mail->FromName = 'Moodle no reply';
        //Antwortadresse
        $mail->addReplyTo($CFG->noreplyaddress, 'Moodle no reply');
//        $mail->addCC('cc@example.com');
//        $mail->addBCC('bcc@example.com');
        //Set email format to HTML
        $mail->isHTML(true);  
        //Zeichensatz auf utf8 setzen
        $mail->CharSet = 'utf-8';        
        //Betreff
        $mail->Subject = 'Moodle';

        //Emailadressen der User zum Mailobjekt hinzufügen
        foreach ($userObjects as $userObject) {
            //Emailadresse des Users hinzufügen
            $mail->addAddress($userObject->email);
            //Zeit seit letzten Login des Users ermitteln
            //->wird nicht benötigt -> mail wird nach $days tagen inaktivität verschickt was ca. dem lastlogin entspricht
            //$zeitohnelogin = $date - $UserObject->lastaccess; 
        }
        //Nachricht (HTML)
        //$mail->Body    = 'Bitte mal wieder in Moodle <b>einloggen</b>!';
        $mail->Body = $text_pt1.$days.' Tagen '.$text_pt2;
        //alternative Nachricht (nur Text)
        //$mail->AltBody = 'Bitte mal wieder in Moodle einloggen!'; 
		
        //Emails senden und Erfolg oder Fehler in Array speichern
        if(!$mail->send()) {            
            $success = array('success' => FALSE, 'message' => 'Beim Senden der Emails ist ein Fehler aufgetreten.<br />Fehlerinfo: ' . $mail->ErrorInfo.'<br />');
        }
        else {
            $success = array('success' => TRUE, 'message' => 'Die Emails wurden erfolgreich gesendet.<br />');
        }  
        //Ergebnis zurückgeben
        return $success;
    }    
}

class block_alarm extends block_base {
    
    public function init() {
        //Dem Plugin eine Überschrift zuweisen
        $this->title = get_string('Alarm', 'block_alarm');

    }
    
//    function has_config() {
//        return true;        
//    }    
    
    public function cron() {
        //neues Emailobjekt erstellen
        $emails = new emails();
        //Funktion sendEmails() aufrufen und Rückgabewert in $success speichern        
        $success = $emails->sendEmails();
        if($success['success'] === TRUE){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    public function get_content() {       
        
        if ($this->content !== null) {           
            return $this->content;
        }
        $this->content = new stdClass;        
        
        //neues Emailobjekt erstellen
        //$emails = new emails();
        //Funktion sendEmails() aufrufen und Rückgabewert in $success speichern        
        //$success = $emails->sendEmails();

        //Ergebnis von sendEmails() ausgeben
        //$this->content->text = $success['message'];    
        $this->content->text = "Dieses Block Plugin benötigt keine grafische Ausgabe.";    
        $this->content->footer = "-----";                    
        
        //neues Emailobjekt erstellen
        $emails = new emails();
        //Funktion sendEmails() aufrufen und Rückgabewert in $success speichern        
        $success = $emails->sendEmails();        
        
        return $this->content;
    }

}

