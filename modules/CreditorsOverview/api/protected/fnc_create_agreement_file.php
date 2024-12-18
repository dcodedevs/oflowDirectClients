<?php
if(!function_exists("create_agreement_file")){
	function create_agreement_file($creditorId){
		global $o_main;
		$errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);
		if(!class_exists("TCPDF")){
	        include(dirname(__FILE__).'/../../../CollectingCases/output/includes/tcpdf/tcpdf.php');
		}
        include(dirname(__FILE__).'/../languagesOutput/no.php');
		$v_return = array();
		$s_sql = "SELECT * FROM creditor WHERE id = ? ";
		$o_query = $o_main->db->query($s_sql, array($creditorId));
		$creditor = ($o_query ? $o_query->row_array() : array());
		if($creditor){
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor("");
			$pdf->SetTitle("");
			$pdf->SetSubject("");
			$pdf->SetKeywords("");
			$pdf->SetCompression(true);

			// remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// set some language-dependent strings (optional)
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
			}
			// add a page
			$pdf->AddPage();


			setlocale(LC_TIME, 'no_NO');
			$pdf->SetFont('calibri', '', 11);
			$pdf->SetY(10);
			// $pdf->MultiCell(100, 0, "<b>".$formText_Client_api."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
			$pdf->MultiCell(100, 0, $creditor['companyname'], 0, 'L', 0, 1, '', '', true, 0, true);
			$pdf->MultiCell(100, 0, $creditor['companypostalbox'], 0, 'L', 0, 1, '', '', true, 0, true);
			$pdf->MultiCell(100, 0, $creditor['companyzipcode']." ".$creditor['companypostalplace'], 0, 'L', 0, 1, '', '', true, 0, true);
			$pdf->MultiCell(100, 0, $formText_OrgNr_pdf.": ".$creditor['companyorgnr'], 0, 'L', 0, 1, '', '', true, 0, true);


			$s_sql = "SELECT * FROM ownercompany";
			$o_query = $o_main->db->query($s_sql);
			$ownercompany = ($o_query ? $o_query->row_array() : array());

			$companyNamePdf = $ownercompany['companyname'];
			$companyAddress = $ownercompany['companypostalbox'].", ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
			$companyPhone = $ownercompany['companyphone'];
			$companyOrgNr = $ownercompany['companyorgnr'];
			$companyEmail = $ownercompany['companyEmail'];

			$pdf->SetY(10);
			// $pdf->MultiCell(70, 0, "<b>".$formText_CollectingCompany_api."</b>", 0, 'R', 0, 1, '', '', true, 0, true);
			$pdf->MultiCell(70, 0, $companyNamePdf, 0, 'R', 0, 1, 125, '', true, 0, true);
			$pdf->MultiCell(70, 0, $companyAddress, 0, 'R', 0, 1, 125, '', true, 0, true);
			$pdf->MultiCell(70, 0, $formText_OrgNr_pdf.": ".$companyOrgNr, 0, 'R', 0, 1, 125, '', true, 0, true);
			$pdf->MultiCell(70, 0, $companyPhone, 0, 'R', 0, 1, 125, '', true, 0, true);
			$pdf->MultiCell(70, 0, $companyEmail, 0, 'R', 0, 1, 125, '', true, 0, true);

			$pdf->Ln(5);
			$html = '<h1 style="text-align: center">Avtale om Inkassotjenester</h1>';
			$html.= "<p>Oflow AS (heretter Oflow) har gitt fullmakt til 24SevenOffice Norway AS  (heretter 24SevenOffice) til å inngå avtaler med tredjepart på deres vegne om leveranse av tjenestene beskrevet i denne avtalen. Avtalen forvaltes kommersiellt av 24SevenOffice på vegne av Oflow, men det er Oflow som er ansvarlig for leveransen av tjenesten. </p>";
			$html.= "<p>Partene i denne avtalen er Oflow og klient. Klient er i denne sammenhengen kunde av 24SevenOffice som gjennom denne avtalen blir Kunde og oppdragsgiver i avtale med Oflow. Begrepet “Kunde” er videre klientens kunder, også benevnt som Skyldner eller Debitor ved mislighold. </p>";
			$html.= "<p>Partenes rettigheter og plikter reguleres av denne avtalens bestemmelser</p>";
			$html.= "<h2>Tjenester</h2>";
			$html.= "<p>Avtalen gjelder leveranse av tjenester som omfatter inkassotjenester fra Oflow AS til klient. </p>";
			$html.= "<p>Generelle tjenester:</p>";
			$html.= "<ul>
				<li>Online innsynsløsning for Klient og Klients kunde (Skyldner)</li>
				<li>Overføring av saker</li>
				<li>Remittering</li>
				<li>Fakturering</li>
				<li>Utenlandsinkasso</li>
			</ul>";
			$html.= "<p>Inkasso</p>";
			$html.= "<ul>
				<li>Utenrettslig inkasso</li>
				<li>Rettslig inkasso, ordinære inkassosaker, inkasso, tvistesaker og spesialsaker</li>
				<li>Juridisk bistand</li>
				<li>Utkonvertering/trekking av sak</li>
			</ul>";
			$html.= "<p>Overvåking</p>";
			$html.= "<ul>
				<li>Overvåk</li>
				<li>Generell saksbehandling</li>
			</ul>";
			$html.= "<p>Les mer om priser og nærmere beskrivelser av tjenesten på www.oflow.no</p>";
			$html.= "<p>Dekningsrekkefølge</p>";
			$html.= "<ol>
				<li>Udekket, utlagte gebyrer og omkostninger</li>
				<li>Oflows salærer</li>
				<li>Klientens hovedkrav</li>
				<li>Klientens forsinkelsesrenter på hovedkrav</li>
				<li>Oflows forsinkelsesrenter på salærer</li>
			</ol>";
			$html.= "<p>Kommunikasjon</p>";
			$html.= "<p>Klienten vil ha tilgang til innsynsløsning hvor all rapportering og oppfølging av sakene vil kunne hentes ut av Klienten. Det kan eksempelvis være status, aktivitet og korrespondanse på sak. Klienten vil også kunne benytte e-post: kundekontakt@oflow.no dersom det er kommunikasjon som ikke er knyttet opp mot en konkret sak.</p>";

			$html.= "<p>Prosesser</p>";
			$html.= "<p>Purresaker overføres automatisk til inkasso når lovverket tillater det eller etter avtale.
			<br/>Inkassosaker legges til overvåk når saken ikke er løst etter 12 mnd., eller det er dokumentert insolvens enten ved negativ utleggsforretning, ved betalingsanmerkninger eller ved interne data på kunden. Saker som er gjenstand for rettslig innfordring, eller som inngår i en gjeldsordning, dødsbo eller konkurs, overføres som spesialsaker og faktureres i henhold til avtalte priser på dette. Oflow melder kravene og følger opp til saken er avsluttet eller betalt i henhold til sluttberetning. Ved konkurs legges saken til Overvåk. Les mer om prosessen på www.oflow.no
			</p>";
			$html.= "<h2>Partenes rettigheter og plikter</h2>";
			$html.= "<p>Partene skal drive sin virksomhet i overensstemmelse med lover og forskrifter.
			<br/>Partene plikter å meddele skriftlig om endringer i systemer, oversendelsesdata, kravgrunnlag og relevante rutiner. Eventuelle feil i oversendelse skal rapporteres umiddelbart.
			<br/>Kravspesifikasjon for utveksling av data, transaksjoner og saker følger standardspesifikasjon og eventuelle tilpasninger skal avtales spesielt i oppstartsfasen. Eventuelle manuelle rutiner, systemutbedringer eller utviklingskostnader ligger hos den enkelte part å utvikle.
			<br/>Ingen av partene kan overdra sine forpliktelser til en tredjepart uten skriftlig samtykke fra den annen part.
			</p>";
			$html.= "<h3>Oflows forpliktelser</h3>";
			$html.= "<p>Oflow forplikter seg til å innkassere forfalte fordringer og levere avtalens tjenester etter gjeldende, relevante lover og forskrifter, herunder også kravet til god inkassoskikk.</p>";
			$html.= "<p>Oflow forbeholder seg retten til å vurdere hvilke inkassotiltak som er nødvendige, og om tiltakene er i samsvar med god inkassoskikk. Dersom det ved mottagelse av et oppdrag, eller på et senere tidspunkt fremkommer opplysninger som etter Oflow sin vurdering vil medføre at det strider mot god inkassoskikk å fremme saken, kan oppdraget avvises eller saksbehandlingen avsluttes som trukket sak. Oflow plikter å holde seg faglig oppdatert og til enhver tid inneha de nødvendige tillatelser og bevillinger for å drive inkasso.
			<br/>Oflow plikter å ha en betryggende risikostyring og internkontroll, herunder sikkerhetsrutiner som beskytter mot katastrofer og forebygger uautorisert tilgang og misbruk.
			<br/>Oflow plikter å holde klientmidler adskilt på egen konto med avregning etter avtalt frekvens. Innkasserte inkassosalær og renter av innkasserte midler tilfaller Oflow, jfr. Inkassolovens § 3 og § 16. Klient frafaller inkassolovens § 15 krav om skriftlig oppgave ved avslutning av sak. Oflow har rett til å motregne i klientmidler for sitt vederlag og dekning av kostnader etter denne avtalen. Dette gjelder også tilgodehavende knyttet til andre oppdrag enn hva innbetalingen fra Skyldner refererer seg til i den enkelte sak.
			</p>";
			$html.= "<h3>Klientens forpliktelser</h3>";
			$html.= "<p>Klienten forplikter seg til å kun oversende reelle, rettmessige og forfalte krav til Oflow for innfordring. I henhold til inkassolovens §9 skal skriftlig inkassovarsel etter kravets forfall ha blitt sendt med 14 dagers betalingsfrist, regnet fra det tidspunkt varselet ble sendt. Dersom gebyr avkreves Skyldner i inkassovarselet, kan slikt varsel først sendes 14 dager etter kravets forfall. </p>";
			$html.= "<p><b>Oflow kan etter avtale sende inkassovarsel på vegne av Klienten.</b></p>";
			$html.= "<p>Klient er ansvarlig for at kravet er oppdatert og korrekt og må minimum inneholde korrekt informasjon om:</p>";
			$html.= "<ul>
				<li>Kundenummer</li>
				<li>Personnummer / Organisasjonsnummer</li>
				<li>Navn</li>
				<li>Folkeregistret adresse</li>
				<li>Fakturanummer</li>
				<li>Forfallsdato</li>
				<li>Hovedstol</li>
				<li>Renter</li>
				<li>Omkostninger</li>
				<li>Renteperiode</li>
				<li>Eventuelt avtalt forsinkelsessats</li>
				<li>Eventuelle innsigelser</li>
				<li>Kopi gjeldsbrev, salgspant, pantobligasjoner, garantier</li>
				<li>Kopi av siste faktura (såkalt skriftstykke)</li>
				<li>Eventuelt annen dokumentasjon som kan anses som viktig for videre behandling</li>
			</ul>";
			$html.= "<p>Klient plikter videre å informere Oflow om forhold som gir grunn til tvil om kravet er rettmessig ved oversendelse av kravet. Klient plikter å oppdatere Oflow løpende om endringer etter oversendelse av kravet. Det innebærer blant annet, men ikke begrenset til; direkte betalinger, innsigelser, andre reduksjoner på kravet, direkte kommunikasjon med kunde og avtaler gjort direkte med kunden. </p>";
			$html.= "<p>Dersom Klient selv inngår f. eks nedbetalingsplan, eller avtale om redusert oppgjør, direkte med Skyldner/debitor, kan Oflow avslutte saken og belaste utestående salærer, omkostninger og gebyrer. Dersom Klient ikke oppdaterer Oflow innen rimelig tid, og det påløper salær og/eller kostnader som ikke kan avkreves Skyldner, må Klient dekke disse inn for egen regning.</p>";
			$html.= "<p>Omtvistede krav skal dokumenteres og merkes særskilt. I saker hvor Oflow ber om uttalelser, plikter Klient å besvare henvendelsen innen 14 dager. Hvis ikke kan det gjeldende oppdrag avsluttes og belastes med 25 % av påløpt salærer, eventuelt 100 % salær dersom saken befinner seg til Overvåk.</p>";
			$html.= "<p>Oflow forbeholder seg retten til å gjennomgå Klients rutiner forut for oversendelse til inkasso. Dette for å sikre at rutinene er i tråd med inkassoloven og forskriftene. Ved oversendelse av en ikke ubetydelig mengde urettmessige krav, eller gjentatte brudd på fastsatte rutiner for oversendelse, herunder mangelfull/feil informasjon om kravet eller om skyldner, kan Oflow si opp avtalen.</p>";
			$html.= "<p>Dersom Klient uaktsomt eller med forsett oversender krav som er grunnløse, feil, eller som påfører Oflow AS direkte eller indirekte tap, eller fare for slikt tap, er Klient ansvarlig for Oflow økonomiske tap som følge av den uaktsomme eller forsettlige handlingen. Ved slike feil som nevnt vil dette også være grunnlag for at avtalen kan heves av Oflow. </p>";

			$html.= "<h3>Fullmakter</h3>";
			$html.= "<p>Oflow skal så langt det lar seg gjøre innenfor rammevilkårene for inkassovirksomhet, sørge for at Klient får betalt mest mulig av kravet så raskt som mulig. For å oppnå dette, er det viktig å ha etablerte fullmakter.</p>";
			$html.= "<p>Oflow sine saksbehandlere kan på skjønnsmessig og generell basis inngå avdragsordning, stille saken i bero, sende rettslig skritt eller overføre saken til Overvåk.
			<br/>Oflow kan ettergi inntil alle renter uten å innhente ytterligere fullmakt. Slik ettergivelse forutsetter en avtale med Skyldner om at renter fastholdes ved mislighold. Dersom det foreligger en rettslig avgjørelse, må Oflow og Klient forholde seg til denne.
			<br/>Oflow kan i spesielle tilfeller avskrive deler av hovedstol. Det gjelder ved gjeldsordning, konkurs og dødsbo. I tillegg kan det gis spesifikke fullmakter fra Klient etter behov og avtale.
			</p>";
			$html.= "<p>Saker med resterende hovedstol under kr. 10.000,- er å anse som småsaker. Disse kan avsluttes uten rettslig oppfølging/ tiltak som avbryter foreldelsesfrist uten å forespørre Klient, dersom Oflow anser at det er liten mulighet for å innbetaling på kravet. Her vil det også være mindre aktivitet for å få til avtale med kunden underveis i inkassoprosessen.</p>";
			// $html.= "<p>Dersom disse sakene skal sendes rettslig skal det innhentes samtykke fra Klient. Alternativt kan Oflow overta omkostningsrisikoen ved rettslig skritt. Ved overtakelse av omkostningsrisikoen ved rettslige skritt, er det opp til Oflow å beslutte om saken skal avsluttes eller forfølges videre.</p>";
			$html.= "<p>Alle saker med hovedkrav over kr. 10.000,- er å anse som betydelig sak. Det betyr økt aktivitet og oppfølging. Minst 6 måneder før foreldelse av et krav over Kr. 10.000,- skal saken vurderes av saksbehandler og en anbefaling skal gis til Klient.</p>";

			$html.= "<h3>Klientmidler og avregning</h3>";
			$html.= "<p>Klientmidler innbetales og oppbevares på særskilt klientkonto tilhørende Oflow, jfr Forskrifter til Inkassolovens §4-1.</p>";
			$html.= "<p>Oflow forplikter seg til å ha tilstrekkelig sikkerhetsstillelse til enhver tid, jf. inkassoforskriften § 3-4.</p>";
			$html.= "<p>Innkasserte midler avregnes iht avtale og remitteres månedlig eller etter avtale.</p>";
			$html.= "<p>Renter av klientmidler tilfaller Oflow. </p>";
			$html.= "<p>Offentlige gebyr, inntil kr. 20.000,- ved rettslige skritt, vil bli forskuttert av Oflow og faktureres månedlig. Beløp ut over dette må avtales i hvert enkelt tilfelle.</p>";

			$html.= "<h3>Merverdiavgiftspliktig</h3>";
			$html.= "<p>Oflow er merverdiavgiftspliktig (mva) og det beregnes mva av alle inntekter knyttet til saker som behandles for Klient. Dette belastes oppdragsgiver, men utgjør ingen kostnad for de oppdragsgivere som er mva pliktige. Oflow forbeholder seg retten til å kunne motregne dette mot utbetalinger til oppdragsgiver for å minimere brevutsendelser og administrasjon for begge selskap.</p>";

			$html.= "<h3>Taushetsplikt og Personopplysninger</h3>";
			$html.= "<p>Oflow er som inkassoforetak behandlingsansvarlig for personopplysninger som behandles under inkassovirksomheten, og forplikter seg til å behandle personopplysninger i samsvar med lov om behandling av personopplysninger, herunder bransjestandarder for inkassobransjen.</p>";
			$html.= "<p>Begge parter er underlagt taushetsplikt. Personopplysninger skal ikke videreformidles til tredjepart, med mindre det er særskilt hjemmel for dette, herunder, men ikke begrenset til: </p>";

			$html.= "<ul>
				<li>Det er nødvendig for å overholde denne avtalen</li>
				<li>Det kreves etter loven</li>
				<li>Det er en profesjonell rådgiver, revisor, bankforbindelse eller offentlig tilsynsmyndighet, som er underlagt tilsvarende taushetsplikt.</li>
			</ul>";
			$html.= "<p>Ved innhenting av kredittopplysning om privatperson eller enkeltpersonforetak vil det gå ut gjenpartsbrev fra kredittopplysningsbyrået, jf. konsesjon for kredittopplysningsvirksomhet gitt av Datatilsynet. Dette gjelder så sant det ikke allerede er sendt eller foreligger noen endringer på kunden.</p>";
			$html.= "<p>Begge parter er ansvarlige for å ta nødvendige skritt for å sikre konfidensiell informasjon, også om innholdet i denne avtalen. Det innebærer at:</p>";

			$html.= "<ul>
				<li>Ingen uautoriserte personer får tilgang til informasjonen</li>
				<li>Dersom det er mistanke om at uautoriserte personer har fått eller har tilgang til slik informasjon, plikter parten å melde fra til annen part samt sikre at tilgangen stoppes umiddelbart</li>
				<li>Ved eventuelt opphør av avtalen skal ingen av partene kunne benytte seg av konfidensiell informasjon i etterkant til eget bruk eller brukt mot andre kunder, leverandører eller samarbeidspartnere.</li>
				<li>All konfidensiell informasjon kan kreves returnert eller slettet, med mindre informasjonen ikke er underlag lovpålagt oppbevaringsplikt. </li>
			</ul>";
			$html.= "<p>Begge parter plikter å bevare taushet om innholdet i denne avtalen. Dette er ikke til hinder for at innsyn gis til Finanstilsynet og andre evt. offentlige institusjoner som har hjemmel til slikt innsyn. </p>";
			$html.= "<h3>Mislighold</h3>";
			$html.= "<p>Dersom en av partene vesentlig misligholder sine forpliktelser, og ikke retter opp forholdet innen to uker etter skriftlig (brev/ e-post) påkrav, kan den annen part heve avtalen med umiddelbar virkning. </p>";
			$html.= "<p>Manglende oppgjør av klientens betalingsplikt, som har medført utsendelse av inkassovarsel, regnes som vesentlig mislighold og gir rett til umiddelbar heving av avtalen. Forsinkelsesrentene er 1,5% pr mnd.</p>";
			$html.= "<p>Dersom Klienten bytter leverandør av tjenester som er omhandlet av denne avtalen, uten å informere om dette på forhånd, anses dette som vesentlig mislighold.
			<br/>Ved heving av avtalen skal Klient betale Oflows salær og evt. påløpte gebyrer frem til opphør av avtalen, samt betale evt. kostnader for Oflow som følge av at avtalen avvikles.</p>";

			$html.= "<h3>Ansvarsbegrensning</h3>";
			$html.= "<p>Oflow kan kun gjøres ansvarlig for det direkte økonomiske tap oppdragsgiveren lider som følge av grov uaktsomhet eller forsett hos Oflow og kan således ikke under noen omstendighet gjøres ansvarlig for følgeskader, herunder for Klientens indirekte tap.
			<br/>Ansvaret er under enhver omstendighet begrenset til det vederlag som Klienten har betalt etter avtalen de siste 12 månedene før den omstendighet eller de omstendigheter som har påført Klienten tapet oppsto, dog oppad begrenset til Kr. 50 000,- dersom dette beløpet er lavere.
			</p>";
			$html.= "<p>Klienten må reklamere snarest og senest innen 30 dager etter at feilen ble oppdaget eller burde blitt oppdaget, og under enhver omstendighet fremme erstatningskrav senest innen 3 måneder etter at feil oppsto. Alle erstatningskrav som fremmes senere enn 3 måneder etter at feilen oppsto fraskriver Oflow seg således ett hvert ansvar for.</p>";
			$html.= "<p>Dersom avtalens gjennomføring helt eller delvis hindres, eller i vesentlig grad vanskeliggjøres av forhold som ligger utenfor partenes kontroll, suspenderes partenes forpliktelser i den utstrekning forholdet er relevant, og for så lang tid som forholdet varer.</p>";
			$html.= "<p>Slike forhold inkluderer, men er ikke begrenset til streik, lockout, og ethvert forhold som etter norske forhold vil bli bedømt som Force Majeure. Partene skal dekke sine egne omkostninger som skyldes Force Majeure. </p>";

			$html.= "<h3>Endringer i avtalen og avtaleperiode</h3>";
			$html.= "<p>Avtalen regulerer samarbeidet så lenge dette løper. Oflow kan endre priser og andre betingelser i Avtalen ved skriftlig eller digitalt varsel. Ved endringer får Klient oppdaterte priser eller tillegg til avtalen oversendt. Dette vil ikke kreve ny underskrift eller aksept fra Klienten.  Oppdaterte priser og vilkår vil til enhver tid ligge på www.oflow.no.</p>";
			$html.= "<p>Avtalen gjelder i ett år fra den dato den er akseptert av begge parter. Den blir deretter automatisk fornyet for ett år av gangen. Begge parter kan skriftlig si opp avtalen senest 30 dager før avtaleperiodens utløp. Dersom Klient avslutter kundeforholdet med 24SevenOffice vil denne avtalen løpe frem til Klient sier opp denne avtalen iht gjeldene avtaleforhold og frister.
			<br/>Ved oppsigelse av avtale, ferdigstilles oppdragene i henhold til avtalen. Ved avslutning av oppdrag før ferdigstillelse, vil Klienten bli belastet iht. punktet om “Utkonvertering/Trekking av sak” under pkt. «økonomi» i denne avtalen.
			</p>";
			$html.= "<p>Ved heving av avtalen skal avvikling av allerede registrerte inkassooppdrag skje etter bestemmelsene om “Utkonvertering/Trekking av sak” under pkt. «økonomi» i denne avtalen.
			<br/>Ved pålegg fra offentlig myndighet, endringer i lovgivning eller i rammebetingelser og ved forhold som påvirker datakvalitet, kredittrutiner, samarbeidsform, kan Oflow endre prisene og tjenestegrunnlag for å reflektere disse endringene/påleggene.</p>";
			$html.= "<h3>Verneting</h3>";

			$html.= "<p>Tvist mellom Oflow og Klient skal søkes løst ved forhandlinger. Dersom forhandlinger ikke fører frem skal tvisten løses av norske domstoler etter norsk rett med Oslo Forliksråd/Tingrett som verneting.</p>";


			$pdf->writeHTML($html, true, false, true, false, '');

			$s_filename = 'uploads/protected/'.$formText_AgreementFile_output.'_'.$creditor['id'].'.pdf';
			$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');
			$v_return['file'] = $s_filename;
		} else {
			$errors[] = $formText_MissingCreditor_output;
		}
        if(count($errors) > 0){
            $v_return['errors'] = $errors;
        }
        return $v_return;
	}
}
?>
