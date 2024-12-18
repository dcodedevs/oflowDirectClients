<?php
require_once __DIR__ . '/ApiCallDriver.php';

class IntegrationJCloud {
    function __construct($config) {
        session_destroy();
        $this->ownercompany_id = isset($config['ownercompany_id']) ? $config['ownercompany_id'] : 0;
        $this->o_main = $config['o_main'];
        $this->options = array ('trace' => true , 'encoding'=>' UTF-8');

        $config = $this->get_local_config();
        if($config['productionMode']){
            $url = "https://webapp.udc.no";
        } else {
            $url = "https://webapp.udc.no";
        }
        $config = $this->get_local_config();
        if($config){
            $this->api = new ApiCallDriver($url);
            $this->api->set_auth_token(base64_encode($config['username'].":".$config['password']));
        }
    }

    /**
     * API session handing
     * this auth() is taken from sample
     */


    function get_local_config() {
        $config = array();
        $s_sql = "SELECT * FROM integrationjcloud";
        $o_query = $this->o_main->db->query($s_sql);
        if($o_query && $o_query->num_rows()>0) $config = $o_query->row_array();

        // Return
        return $config;
    }
    function test_connection(){
        $result = $this->api->get('/distribution');

        $resultArray = $result;
        return $resultArray;
    }
    function send_reminder($data){
        $resultArray = array();
        $config = $this->get_local_config();
        if($config){
            if($config['productionMode']){
		        $data['methods'] = array("snailmail");
			} else {
		        $data['methods'] = array("email");
			}
            $result = $this->api->post('/distribution', $data);
            $resultArray = json_decode($result,TRUE);
            if(json_last_error() != JSON_ERROR_NONE){
                $resultArray = $result;
            }
        }
        return $resultArray;
    }

    function send_print_zip($data){
        $resultArray = array();
        $config = $this->get_local_config();
        if($config){
            $transferID = $config['transfer_id'];
            $transferKey = $config['transfer_key'];
            if(file_exists($data['zip_file_path'])){
                if($transferID != "" && $transferKey != ""){
                    $software = "Standard";
                    $software_version = "1.0";
                    $timestamp = date("YmdHis");
                    $shaCalculatedPrep = $software."+".$software_version."+".$transferID."+".$timestamp."+".$transferKey;

                    $handle = fopen($data['zip_file_path'], "rb");
                    $filter = fread($handle, filesize($data['zip_file_path']));
                    fclose($handle);

                    $result = $this->api->put('/print?soft='.$software."&ver=".$software_version."&TraID=".$transferID."&t=".$timestamp."&d=SHA-256:".hash('sha256', $shaCalculatedPrep), $filter);
                    $xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json = json_encode($xml);
                    $resultArray = json_decode($json,TRUE);
                }
            }
        }

        return $resultArray;
    }
}

?>
