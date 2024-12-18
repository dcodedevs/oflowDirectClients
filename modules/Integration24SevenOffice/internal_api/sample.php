<?php
      session_start();
      $username = "username";  //Change this to your client user or community login
      $password = "password";  //Change this to your password
      $applicationid = "77cf551c-3a8b-499c-9526-27facb86a9c5";  //Change this to your applicationId
      $options = array ('trace' => true );
      $params ["credential"]["Username"] = $username;
      $encodedPassword = md5(mb_convert_encoding($password, 'utf-16le', 'utf-8'));
      $params ["credential"]["Password"] = $encodedPassword;
      $params ["credential"]["ApplicationId"] = $applicationid;

      $params ["credential"]["IdentityId"] = "00000000-0000-0000-0000-000000000000";

      try {
          $authentication = new SoapClient ( "https://api.24sevenoffice.com/authenticate/v001/authenticate.asmx?wsdl", $options );
          // log into 24SevenOffice if we don't have any active session. No point doing this more than once.
          $login = true;
          if (!empty($_SESSION['ASP.NET_SessionId']))
          {
              $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
              try
              {
                   $login = !($authentication->HasSession()->HasSessionResult);
              }
              catch ( SoapFault $fault )
              {
                  $login = true;
              }
          }
          if( $login )
          {
              $result = ($temp = $authentication->Login($params));
              // set the session id for next time we call this page
              $_SESSION['ASP.NET_SessionId'] = $result->LoginResult;
              // each seperate webservice need the cookie set
              $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
              // throw an error if the login is unsuccessful
              if($authentication->HasSession()->HasSessionResult == false)
                  throw new SoapFault("0", "Invalid credential information.");
          }
          // To get current identity:
          // print_r($authentication->GetIdentity());
          // To connect to another webservice:
          //*(SuperService is a dummy example webservice, for your integration you would want to change this for any real webservice we offer)
          $superService = new SoapClient ( "https://api.24sevenoffice.com/superservice/super.asmx?wsdl", $options );
          $superService->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
          $superResult = $superService->GetSuperList();
      }
      catch ( SoapFault $fault )
      {
          echo 'Exception: ' . $fault->getMessage();
      }
   ?>
