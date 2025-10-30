<?php

return [
    'ccc' => [
        'imgUpload' => 'Upload für Logo und Hintergrundbild. Bitte setzen Sie nach dem Upload das entsprechende Bild!',
    ],
    'dateFormat' => 'd.m.Y',
    'dateTimeFormat' => 'd.m.Y H:i:s',
    'debt' => [
        'amount' => 'Postiv wenn Kunde Geld abgezogen wird, negativ wenn Kunde einzahlt',
        'debtToClear' => 'Hier werden nur OPs angezeigt, deren absoluter ausstehender Betrag größer ist als der der hier geöffneten Transaktion.',
        'totalFee' => 'Veraltete Anzeige. Dient nur der Anzeige der bisher genutzten Gesamtgebühr. Diese wird jetzt aus der Summe von Bank- und zusätzlicher Gebühr berechnet und in Zukunft wegen Redundanz nicht mehr angezeigt.',
    ],
    'rkmServerAddress' => 'IP:Port (IP kann ein Hostname sein). Wird ein globaler Server eingetragen, gilt dieser für ALLE Abzweiger! Bitte erstellen Sie Netzelemente vom Typ RKM-Server, wenn Sie mehrere RKM-Server besitzen. Der zugehörige Server wird dann über die Elternbeziehung im Netzelement bzw. ERD ermittelt.',

    /*
     * Authentication and Base
     */
    'translate'                 => 'Sie können dabei helfen NMS Prime zu übersetzen. Besuchen Sie:',
    'assign_role'                   => 'Diesem Nutzer eine oder mehrere Rollen zuweisen. Nutzer ohne Rolle können das NMS nicht verwenden, da sie keine Berechtigungen haben.',
    'assign_users'                  => 'Einen oder mehrere Nutzer zu dieser Rolle zuweisen. Die Veränderung ist im GuiLog des Users nicht sichtbar, sondern nur hier.',
    'assign_rank'                   => 'Der Rang einer Rolle gibt die Fähigkeiten der Rolle an, andere Nutzer zu bearbeiten.<br \\>Es werden werte von 0 bis 100 angenommen. (höher ist besser)<br \\>Hat ein Nutzer mehrere Rollen, gilt der höchste Rang.<br \\> Wenn die Fähigkeit gesetzt ist um Nutzer bearbeiten zu können, wird außerdem der Rang geprüft. Nur wenn der Rang des Bearbeiters höher ist, wird die Anfrage genehmigt. Weiterhin können beim erstellen und bearbeiten von Nutzern nur Rollen mit dem gleichen oder einem niedrigeren Rang vergeben werden.',
    'All abilities'                 => 'Diese Fähigkeit erlaubt alle Autorisierungsanfragen, außer es wurden explizit Fähigkeiten verboten. Diese Fähigkeit ist besonders nützlich, wenn eine Rolle sehr viel mit wenigen Ausnahmen "darf". Das Verbieten der Fähigkeit wurde deaktiviert, da es keine Auswirkungen hat (nur ausgewählte Fähigkeiten sind erlaubt). Wenn diese Fähigkeit nicht gesetzt ist, müssen alle Berechtigungen von Hand gesetzt werden. Das Ändern dieser Fähigkeit, wenn schon viele andere Fähigkeiten gesetzt sind, kann bis zu einer Minute dauern.',
    'countryCode'               => 'ISO 3166 ALPHA-2 (zwei Buchstaben). Wird für die Bestimmung der Koordinaten benötigt, kann aber frei gelassen werden, wenn der global eingetragene Standard-Ländercode genutzt werden soll.',
    'View everything'           => 'Diese Fähigkeit erlaubt es alle Seiten zu besuchen. Das Verbieten der Fähigkeit wurde deaktiviert, da in diesem Fall alle Fähigkeiten verboten werden sollten. Diese Fähigkeit ist hauptsächlich zur Hilfe da, um schnell Rechte für Gäste oder Benutzer mit nur sehr wenigen Privilegien zu setzen.',
    'Use api'                   => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die API Routen mithilfe von "Basic Auth". Als Benutzername muss die E-Mail, welche im Profil hinterlegt ist genutzt werden.',
    'See income chart'          => 'Diese Fähigkeit erlaubt oder verbietet die Anzeige des Einkommensdiagramms im Dashboard.',
    'View analysis pages of modems' => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die Analysisseiten der Modems.',
    'View analysis pages of netgw' => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die Analysisseite der NetGws.',
    'Download settlement runs'  => 'Diese Fähigkeit erlaubt oder verbietet den Download der Abrechnungsläufe. Wenn das Verwalten von Abrechnungsläufen verboten ist, hat diese Einstellung keine Auswirkung.',
    /*
     * Index Page - Datatables
     */
    'SortSearchColumn'              => 'Diese Spalte kann nicht sortiert oder durchsucht werden.',
    'PrintVisibleTable'             => 'Druckt den aktuell sichtbaren Bereich der Tabelle. Um alles zu drucken bitte im Filter \\"Alle\\" auswählen. Das Laden kann einige Sekunden dauern.',
    'ExportVisibleTable'            => 'Exportiert den aktuell sichtbaren Bereich der Tabelle. Um alles zu exportieren bitte im Filter \\"Alle\\" auswählen. Das Laden kann einige Sekunden dauern.',
    'ChangeVisibilityTable'         => 'Mit dieser Option können Spaten ein-/ausgeblendet werden.',
    'clearFilter'                   => 'Suchfilter für Spaltensuche und das allgemeine Suchfeld leeren.',

    // GlobalConfig
    'ISO_3166_ALPHA-2'              => 'ISO 3166 ALPHA-2 (zwei Zeichen, z.B. „DE“). Genutzt in Formularen mit Adressdaten um das Land anzugeben.',
    'PasswordReset'                 => 'Diese Einstellung bestimmt, in welchem Intervall die Nutzer des Administrationsbereiches zum Ändern ihres Passworts aufgefordert werden. Setzen Sie diesen Wert auf 0, um Passwörter unendlich lang gültig zu halten.',
    'syncProvision'                 => 'Nur für TR-069: Diese Einstellung bestimmt, ob ein weiterer Knopf auf der Einstellungsseite des Modem angezeigt wird, welcher die Konfigurationsdatei mit dem Modem synchronisiert. Dadurch kann das Modem neue Einstellungen (z.B. eine neue Telefonnummer) übernehmen, ohne das es auf Werkseinstellungen zurückgesetzt werden muss.',

    // CompanyController
    'Company_Management'            => 'Trennung der Namen durch Komma.',
    'Company_Directorate'           => 'Trennung der Namen durch Komma.',
    'Company_TransferReason'        => 'Vorlage aller Rechnungsklassen als Datenfeld-Schlüssel - Vertrags- und Rechnungsnummer sind standardmäßig ausgewählt.',
    'conn_info_template'            => 'TeX Vorlage für das Anschlussinformationsblatt. (Kann auf der Kundenvertragsseite erstellt werden)',

    // ProductRegionRule helper translations
    'product_region_rule' => [
        'scope_ref_id' => 'Referenz-ID für den ausgewählten Bereichstyp (DMA-ID, Stadt-ID, Straßen-ID, etc.)',
        'requires_right_code' => 'Optional: Code eines spezifischen Benutzerrechts, das für den Zugriff auf dieses Produkt erforderlich ist',
        'effective_from' => 'Optional: Startdatum, ab dem diese Regel aktiv wird',
        'effective_to' => 'Optional: Enddatum, bis zu dem diese Regel gültig ist',
        'priority' => 'Prioritätsreihenfolge für die Regelauswertung (niedrigere Zahlen = höhere Priorität)',
    ],

    // ProductLayer helper translations
    'product_layer' => [
        'sequence' => 'Anzeigereihenfolge für Ebenen (niedrigere Zahlen erscheinen zuerst)',
    ],

    // ProductLayerAssignment helper translations
    'product_layer_assignment' => [
        'sort' => 'Sortierreihenfolge innerhalb der Ebene (niedrigere Zahlen erscheinen zuerst)',
    ],

    // ProductConstraint helper translations
    'product_constraint' => [
        'min_qty' => 'Mindestmenge, die erforderlich ist, wenn die Durchsetzung für einen Produkttyp gilt',
    ],

    // ProductAddon helper translations
    'product_addon' => [
        'base_product_id_or_layer' => 'Entweder ein bestimmtes Produkt auswählen ODER eine Produkt-Ebene angeben',
        'product_layer_id_explanation' => 'Produkt-Ebene, für die dieses Addon gilt (Addons werden nach Ebenenabschluss angezeigt)',
        'max_qty' => 'Maximale Menge dieses Add-ons, die ausgewählt werden kann',
        'sort' => 'Anzeigereihenfolge für dieses Addon (niedrigere Zahlen erscheinen zuerst)',
    ],

    // Order Portal Config
    'order_portal_config' => [
        'primary_color' => 'Hex-Farbcode für die primäre Theme-Farbe (z.B. #007bff). Wird für Schaltflächen, Links und wichtige UI-Elemente im Web-Bestellportal verwendet.',
        'secondary_color' => 'Hex-Farbcode für die sekundäre Theme-Farbe (z.B. #6c757d). Wird für sekundäre UI-Elemente und Hintergründe verwendet.',
        'show_header' => 'Zeigt die Kopfzeile/Navigationsleiste im Kunden-Bestellportal an. Deaktivieren Sie diese Option, um die Kopfzeile für eingebettete/iframe-Szenarien auszublenden.',
        'enable_business_customers' => 'Wenn aktiviert, können Kunden bei der Bestellung zwischen Privat- und Geschäftskunde wählen. Wenn deaktiviert, wird diese Option ausgeblendet.',
        'payment_method_sepa' => 'Aktivieren Sie SEPA-Lastschrift als verfügbare Zahlungsmethode für Kunden.',
        'payment_method_rechnung' => 'Aktivieren Sie Rechnung (Überweisung) als verfügbare Zahlungsmethode für Kunden.',
        'payment_method_acs' => 'Aktivieren Sie ACS (Automated Clearing Service) als verfügbare Zahlungsmethode für Kunden.',
        'payment_method_credit_card' => 'Aktivieren Sie Kreditkartenzahlung als verfügbare Zahlungsmethode für Kunden.',
        'postal_invoice_product_id' => 'Wählen Sie das Produkt aus, das für Papierrechnungen verwendet werden soll. Dies sollte ein Produkt vom Typ "Postal" sein. Wird in WebOrder und CCC für die Rechnungszustellung verwendet.',
    ],
    'ccc_config' => [
        'block_internet_downselling' => 'Verhindert, dass Kunden im CCC-Portal einen langsameren Internet-Tarif als ihren aktuellen wählen können. Standard: aktiviert.',
        'payment_method_sepa' => 'Aktivieren Sie SEPA-Lastschrift als verfügbare Zahlungsmethode für Kunden im CCC-Portal.',
        'payment_method_rechnung' => 'Aktivieren Sie Rechnung (Überweisung) als verfügbare Zahlungsmethode für Kunden im CCC-Portal.',
        'payment_method_acs' => 'Aktivieren Sie ACS (Automated Clearing Service) als verfügbare Zahlungsmethode für Kunden im CCC-Portal.',
        'payment_method_credit_card' => 'Aktivieren Sie Kreditkartenzahlung als verfügbare Zahlungsmethode für Kunden im CCC-Portal.',
        'postal_invoice_product_id' => 'Wählen Sie das Produkt aus, das für Papierrechnungen verwendet werden soll. Dies sollte ein Produkt vom Typ "Postal" sein.',
    ],

    // Web Order
    'web_order' => [
        'order_number_help' => 'Kurze, benutzerfreundliche Bestellnummer, die aus dem Hash für Anzeigezwecke generiert wird.',
        'availability_snapshot_help' => 'JSON-Daten mit Verfügbarkeitsprüfungsergebnissen und Service-Optionen für die Kundenadresse.',
        'utm_json_help' => 'JSON-Daten mit UTM-Parametern und Tracking-Informationen aus der Web-Bestellungsquelle.',
        'consent_json_help' => 'JSON-Daten mit Kundeneinverständnis-Informationen und DSGVO-Compliance-Daten.',
        'payment_data_help' => 'JSON-Daten mit verschlüsselten Zahlungsinformationen (IBAN, Kreditkartendetails, etc.) für Verifizierungszwecke.',
    ],

    // CostCenterController
    'CostCenter_BillingMonth'       => 'Abrechnungsmonat für jährliche Posten. Gilt für den Monat für den die Rechnungen erstellt werden. Standard: 6 (Juni) - wenn nicht festgelegt. Bitte seien Sie vorsichtig beim Ändern innerhalb des Jahres: das Resultat könnten fehlende Zahlungen sein!',

    // ItemController
    'item' => [
        'productId'                => 'Alle Felder außer dem Abrechnungszyklus müssen vor eine Änderung des Produkts gelöscht werde! Andernfalls können die Produkte in den meisten Fällen nicht gespeichert werden.',
        'validFrom'                => 'Für einmalige (Zusatz-)Zahlungen kann das Feld genutzt werden, um die Zahlung zu teilen - nur  Jahr und Monat (jjjj.mm) werden berücksichtigt.',
        'validFromFixed'           => 'Dieses Feld ist standardmäßig gesetzt! Deaktivieren Sie diese Checkbox, wenn der Tarif zum gewünschten Startdatum inaktiv bleiben soll (z.B. falls auf einen Portierungstermin gewartet wird). Der Tarif startet damit nicht und wird auch nicht abgerechnet bis Sie die Checkbox aktivieren. Bei Erreichen des Startdatums wird dieses außerdem jeden Tag erneut auf den darauffolgenden Tag gesetzt. Info: Feste Termine werden nicht durch externe Aufträge (z.B. vom Telefonie-Provider) aktualisiert.',
        'validTo'                  => 'Es ist möglich hier die Anzahl der gültigen Monate anzugeben - z.B. \'12M\' für zwölf Monate. Bei monatlich abgerechneten Produkten werden diese 12 Monate zum Startdatum addiert. Bei Start am 2018-05-04 wird das Enddatum der 2019-05-04 sein. Einmalig zu zahlende Produkte, deren Zahlung geteilt wird, werden dann 12 mal abgerechnet - das Enddatum wäre im Beispiel dann 2019-04-31.',
        'validToFixed'             => 'Dieses Feld ist standardmäßig gesetzt! Deaktivieren Sie diese Checkbox, wenn das Enddatum noch ungewiss ist. Der Tarif bleibt damit aktiv und wird weiterhin abgerechnet bis Sie die Checkbox aktivieren. Bei Erreichen des Enddatums wird dieses außerdem jeden Tag erneut auf den darauffolgenden Tag gesetzt. Info: Feste Termine werden nicht durch externe Aufträge (z.B. vom Telefonie-Provider) aktualisiert.',
        'creditAmount'             => 'Überschreibt den Preis des Produktes. Bei Gutschriften: Nettobetrag, der dem Kunde gutgeschrieben werden soll. Achtung: Bei Gutschriften wird ein negativer Betrag dem Kunde abgezogen!',
    ],

    'crmOpportunityItem' => [
        'productId' => 'Produkt, dem der Gelegenheits-Artikel zugeordnet ist. Wählen Sie das entsprechende Produkt für diese CRM-Gelegenheit.',
        'validFrom' => 'Datum, ab dem der Gelegenheits-Artikel gültig sein soll.',
        'validFromFixed' => 'Wenn aktiviert, ist das Datum fixiert und wird nicht geändert.',
        'validTo' => 'Datum, bis zu dem der Gelegenheits-Artikel gültig sein soll. Sie können das Format 12M für 12 Monate ab dem Gültig-von-Datum verwenden.',
        'validToFixed' => 'Wenn aktiviert, ist das Datum fixiert und wird nicht geändert.',
        'creditAmount' => 'Falls gesetzt, wird dieser Betrag anstelle des Produktpreises für diesen Gelegenheits-Artikel verwendet.',
    ],

    // ProductController
    'product' => [
        'billingCycles' => 'Erklärungen zu den Abrechnungszyklen finden Sie in der offiziellen Dokumentation (Web) -> Enterprise Applications -> Prime Billing -> Products',
        'bundle'                => 'Ist der Tarif mit dem Voip-Tarif gebündelt, wird die Gesamtvertragslaufzeit eines Kunden nur anhand des Internet-Tarifs bestimmt. Anderenfalls bestimmt der Tarif (Voip oder Internet) darüber, der zuletzt begonnen hat.',
        'markon'                => 'Extra Aufschlag auf die Einzelverbindungen. Der prozentuale Aufschlag wird aktuell nur zu Opennumbers EVNs addiert.',
        'maturity_min'          => 'Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'maturity'              => 'Laufzeitverlängerung nach der Mindestlaufzeit. <br> Die Gesamtlaufzeit wird automatisch um diese Zeit verlängert, wenn der Tarif nicht vor der Kündigungsfrist gekündigt wurde. Default: 1 Monat. Wird keine Laufzeit angegeben, wird das Laufzeitende des Tarifs immer auf den letzten Tag des Monats gesetzt. <br><br> Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'Name'                  => 'Für Kredite ist es möglich einen Typ zuzuweisen, indem der Typname dem Namen des Kredits angefügt wird - z.B.: \'Kredit Gerät\'',
        'parentId' => 'Wählen Sie hier den Basis-Tarif aus, mit dem dieses Produkt einen Kombinationstarif bildet. Aktuell wird dies nur zur Auswertung für Statistiken genutzt und hat sonst keine weitere Funktion.',
        'pod'                   => 'Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'proportional'          => 'Setzen Sie diesen Haken, wenn Posten, die innerhalb des aktuellen Abrechnungszyklus beginnen, anteilig berechnet werden sollen. Somit würde bei einem monatlich abzurechnenden Produkt mit Beginn in der Mitte des Monats im aktuellen Abrechnungszyklus nur die Hälfte des vollen Preises abgerechnet werden.',
        'Type'                  => 'Alle Felder außer dem Abrechnungszyklus müssen vor einer Änderung des Produkts gelöscht werden! Andernfalls können die Produkte in den meisten Fällen nicht gespeichert werden.',
        'deprecated'            => 'Setzen Sie diesen Haken, wenn das Produkt veraltet ist. Dadurch kann es nicht mehr beim Erstellen/Ändern von Posten ausgewählt werden.',
    ],
    'Product_Number_of_Cycles'      => 'Achtung! Für alle Produkte, die in einem wiederkehrenden Zyklus bezahlt werden steht der Preis für jede einzelne Zahlung. Für Produkte, die einmalig bezahlt werden wird der Preis durch die Anzahl der Zyklen geteilt.',

    // SalesmanController
    'Salesman_ProductList'          => 'Geben Sie alle Produkttypen an, für die der Verkäufer eine Provision erhalten soll. Möglich sind:',

    // SepaMandate
    'sm_cc'                         => 'Tragen Sie hier eine Kostenstelle ein, um über dieses Konto nur Posten/Produkte abzurechnen, die derselben Kostenstelle zugewiesen sind. Dem Konto eines SEPA-Mandats ohne zugewiesene Kostenstelle werden alle entstandenen Kosten abgebucht, die keinem anderen Mandat zugordnet werden können. Anmerkung: Entstehen Kosten, die keinem SEPA-Mandat zugeordnet werden können, wird angenommen, dass diese in bar beglichen werden.',
    'sm_recur'                      => 'Aktivieren, wenn vor dem Anlegen bereits Transaktionen von diesem Konto vorgenommen worden. Setzt den Status auf Folgelastschrift. Anmerkung: Wird nur bei der ersten Lastschrift beachtet!',

    // SettlementrunController
    'settlement_verification'       => 'Die Rechnungen der Kunden werden nur mit aktivierter Checkbox angezeigt. Der Haken kann nur gesetzt werden, wenn der letzte Rechnungslauf für ALLE SEPA-Konten ausgeführt wurde (damit keine Änderungen missachtet werden). Info: Mit aktivierter Checkbox kann der Abrechnungslauf nicht wiederholt werden.',

    /*
     * MODULE: Dashboard
     */
    'next'                          => 'Nächster Schritt: ',
    'set_isp_name'                  => 'Namen des Internetanbieters setzen',
    'create_netgw'                  => 'Erstes NetGw/CMTS anlegen',
    'create_cm_pool'                => 'Ersten Kabelmodem IP-Bereich anlegen',
    'create_cpepriv_pool'           => 'Ersten privaten CPE IP-Bereich anlegen',
    'create_qos'                    => 'Erstes QoS Profil anlegen',
    'create_product'                => 'Erstes Abrechnungsprodukt anlegen',
    'create_configfile'             => 'Erste Konfigurationsdatei anlegen',
    'create_sepa_account'           => 'Erstes SEPA-Konto anlegen',
    'create_cost_center'            => 'Erste Kostenstelle anlegen',
    'create_contract'               => 'Ersten Vertrag anlegen',
    'create_nominatim'              => 'E-Mail Adresse (OSM_NOMINATIM_EMAIL) in /etc/nmsprime/env/global.env eintragen, um die Geolokalisation für Modems zu ermöglichen',
    'create_nameserver'             => 'Den Nameserver in /etc/resolv.conf auf 127.0.0.1 setzen und sicherstellen, dass dieser nicht via DHCP überschrieben wird (siehe DNS und PEERDNS in /etc/sysconfig/network-scripts/ifcfg-*)',
    'create_modem'                  => 'Erstes Modem anlegen',

    /*
     * MODULE: HfcReq
     */
    'netelement' => [
        'credentials' => 'Wird aktuell nur für Netzelemente des Typs \'RKM-Server\' genutzt, um während der Abzweigersteuerung die Verbindung zum Server herzustellen.',
    ],
    'netelementtype' => [
        'reload'        => 'In Sekunden. 0s zum Deaktivieren des Autoreloads. Nachkommastellen möglich.',
        'sidebarPos'    => 'Position in der Sidebar - absteigend. D.h. der Netzelementtyp mit der höchsten Zahl taucht in der Sidebar ganz unten auf. Bitte lassen Sie das Feld leer, wenn der Netzelementtyp nicht in der Sidebar auftauchen soll.',
        'time_offset'   => 'In Sekunden. Nachkommastellen möglich.',
        'undeleteables' => 'Net & Cluster können weder gelöscht werden, noch kann der Name geändert werden, da die Existenz dieser Typen Vorraussetzung für die Erzeugung des Entitity-Relationship-Diagramms ist.',
    ],
    'gpsUpload'                     => 'Eine GPS-Datei vom Typ WKT, EWKT, WKB, EWKB, GeoJSON, KML, GPX oder GeoRSS',

    /*
     * MODULE: HfcSnmp
     */
    'mib_filename'                  => 'Der Dateiname setzt sich aus MIB Name und Revision zusammen. Existiert bereits ein MIB-File mit selbem Dateiname und ist identisch, kann dieses nicht erneut angelegt werden.',
    'oid_link'                      => 'Gehe zu OID Einstellungen',
    'oid_table'                     => 'INFO: Dieser Parameter gehört zu einer Tabellen-OID. Durch Hinzufügen von SubOIDs or Indizes werden die SnmpWerte nur für diese abgefragt. Neben einem besseren Überblick auf der Einstellungen-Übersicht des Netzelements kann dies deren Aufrufgeschwindigkeit deutlich beschleunigen.',
    'parameter_3rd_dimension'       => 'Durch Aktivieren der Checkbox wird dieser Parameter zur Einstellungsseite hinter einem Element in der Tabelle hinzugefügt.',
    'parameter_diff'                => 'Bei Aktivierter Checkbox wird nur die Differenz des aktuell zum zuletzt abgefragten Wert angezeigt.',
    'parameter_divide_by'           => 'Durch Angabe von OIDs wird dieser Wert prozentual zur Summe der zu diesen OIDs abgefragten Werte dargestellt. Dies funktioniert vorerst nur in SubOIDs exakt definierter Tabellen. Die hier angegebenen OIDs müssen als Parameter in der SubOID-List eingetragen sein.',
    'parameter_indices'             => 'Durch Angabe einer durch Kommas getrennten Liste der Indizes der Tabellenreihen, werden die SnmpWerte nur für diese Einträge abgefragt.',
    'parameter_html_frame'          => 'Durch Eintragen einer zweistelligen Framenummer wird der Parameter dem Frame auf der Seite zugewiesen. Durch das Eintragen unterschiedlicher Framenummern bei den Parmetern wird die Seite gemäß der Nummer aufgeteilt. Dabei entspricht die erste Zahl der Zeile und die zweite Zahl der Spalte. Auf SubOIDs von Tabellen hat die Framenummer keinen Einfluss (aber auf 3. Dimension-Parameter!).',
    'parameter_html_id'             => 'Durch Eintragen einer ID wird der Parameter in Reihe zu den anderen Parametern gemäß der ID (aufsteigend) angeordnet. In Tabellen kann durch setzen der ID im Sub-Parameter die Spaltenanordnung verändert werden.',

    /*
     * MODULE: ProvBase
     */
    'contract' => [
        'lastAmendment' => 'Tag an dem die letzte Vertragsänderung vorgenommen wurde. Dies hat Einfluss auf die Vertragsbestätigung. Nur Tarife/Posten, die nach diesem Datum hinzugefügt wurden, werden aufgeführt und bezüglich der Bestimmung Laufzeiten einbezogen.',
        'number' => 'Achtung - Kundenkennwort wird bei Änderung automatisch geändert!',
        'salutation' => 'Zum Ändern der Optionen bitte die Datei(en) '.storage_path('app/config/provbase/formoptions/salutations_').'{person,institution}.txt ändern!',
        'valueDate' => 'Tag im Monat des separaten Buchungsdatums. Überschreibt das Fälligkeitsdatum aus den globalen Konfigurationen für diesen Vertrag in der SEPA XML. Die Bank bucht den Betrag dann an diesem Tag ab.',
    ],
    'rate_coefficient'              => 'MaxRateSustained wird mit diesem Wert multipliziert, um den Nutzer eine höhere (> 1.0) Übertragungsrate als gebucht zu gewähren.',
    'additional_modem_reset'           => 'Zeigt einen zusätzlichen Modem Reset Button an, um das Modem ohne Hilfe des NetGws direkt per SNMP neu zu starten.',
    'auto_factory_reset'            => 'Führt für TR-069 CPEs automatisch einen Werksreset nach Änderung relevanter Einstellungen, die eine Neuprovisionierung erfordern, durch. (Änderung der Telefonnummern, PPPoE Zugangsdaten bzw. der Konfigurationsdatei)',
    'factory_reset_discovered_cpes' => 'Führt einen automatischen Werksreset für neu entdeckte TR-069 CPEs aus',
    'use_radius_relay_info' => 'Authentifiziere IPoE Clients via DHCP Relay Informationen anstelle von PPPoE Zugangsdaten (setze sql_user_name = "%{Agent-Circuit-Id}" in /etc/raddb/mods-config/sql/main/postgresql/queries.conf)',
    'use_framed_pool' => 'IP Adressvergabe wird vom BRAS durchgeführt. Signalisiere über das Framed-Pool RADIUS Attribut von welchem IP Pool (namens CPEPriv oder CPEPub) die IP vergeben werden soll.',
    'global_ont' => 'Provisioniert ein OLT unabhängig von seiner Konnektivität (OLT und Port), Altiplano: Fibername wird nicht benötigt.',
    'acct_interim_interval'         => 'Die Zeit in Sekunden zwischen vom NAS gesendeten Interim Updates (PPPoE).',
    'openning_new_tab_for_modem' => 'Öffnet die Modem-Edit Seite in einem neuen Fenster (Topographie).',
    'ppp_session_timeout'           => 'In Sekunden. Bei einem Wert von 0 werden die PPP Sessions nicht getrennt.',
    'max_cpe' => 'Mindestens 2, Standard 2. Ein Wert von „-1“ entfernt „lease limit“ aus der DHCP-Konfiguration und setzt MaxCPE in DOCSIS-Konfigurationsdateien auf 254.',
    // ModemController
    'modem' => [
        'internetAccess' => 'Internetzugriff für CPEs. (MTAs werden nicht beachtet und gehen immer online, wenn alle restlich notwendigen Konfigurationen korrekt vorgenommen wurden) - Achtung: Mit Billingmodul wird diese Checkbox während der nächtlichen Prüfung (nur) bei Tarifänderung überschrieben. Kann nicht mehr gesetzt werden, wenn Vertrag abgelaufen ist.',
        'qosCount' => 'Die Zahl in Klammern zeigt an, wie häufig der jeweilige QOS bereits verwendet wird.',
        'configfileSelect' => 'Es ist nicht möglich den Typ des Modems über das Configfile zu ändern (z.B. von \'cm\' zu \'tr-69\'). Bitte löschen Sie das Modem dazu und erstellen Sie ein neues!',
        'additional' => 'Hier können zusätzliche Bemerkungen notiert werden.',
    ],
    'Modem_InstallationAddressChangeDate'   => 'Datum der Änderung der Installationsadresse. Wenn nur lesbar existiert bereits ein offener Auftrag.',
    'Modem_GeocodeOrigin'           => 'Quelle der Geodaten. Falls hier „n/a“ steht konnte die Adresse nicht aufgelöst werden. Bei manueller Änderung der Geodaten wird der aktuelle Nutzer eingetragen.',
    'netGwSupportState' => [
        'full-supported' => 'Mehr als 95% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'restricted' => 'Zwischen 80%-95% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'not-supported' => 'Weniger als 80% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'verifying' => 'Weniger als 80% der integrierten Module wurden als unterstützte Geräte gelistet. Das netGw befindet aber noch in der Verifikationszeitspanne von 6 Wochen.',
    ],
    'mac_formats'                   => "Erlaubte Formate (Groß-/Kleinschreibung nicht unterschieden):\n\n1) AA:BB:CC:DD:EE:FF\n2) AABB.CCDD.EEFF\n3) AABBCCDDEEFF",
    'fixed_ip_warning'              => 'Die Nutzung fester IP Adressen ist nicht empfohlen, da hierbei Modems und ihre zugehörigen CPEs nicht mehr zwsichen NetGws verschoben werden können. Anstatt den Endkunden die jeweilige IP Adresse zu nennen, sollte ihnen der Hostname mitgeteilt werden, da sich dieser nicht ändert.',
    'addReverse'                    => 'Zum Setzen eines zusätzlichen Reverse DNS Eintrags, z.B. für E-Mail Server',
    'modem_update_frequency'        => 'Dieses Feld wird einmal täglich aktualisiert.',
    'modemSupportState' => [
        'full-supported' => 'Das Modem wurde als unterstütztes Gerät gelistet.',
        'not-supported' => 'Das Modem wurde nicht als unterstütztes Gerät gelistet',
        'verifying' => 'Das Modem wurde noch nicht als unterstütztes Gerät gelistet, die Verifikationszeitspanne von 6 Wochen ist aber noch nicht abgelaufen.',
    ],
    'enable_agc'                    => 'Aktiviere automatische Verstärkungsregelung in Rückkanalrichtung.',
    'agc_offset'                    => 'Verschiebung des automatischen Verstärkungsregelungwertes in Rückkanalrichtung in dB. (Vorgabewert: 0.0)',
    'configfile_count'              => 'Die Zahl in Klammern zeigt an, wie häufig die jeweilige Konfigurationsdatei bereits verwendet wird.',
    'has_telephony'                 => 'Muss aktiv sein, wenn der Kunde Telefonie haben soll, aber kein Internet hat. Das Flag kann aktuell nicht genutzt werden, um die Telefonie bei Verträgen mit Internet zu deaktivieren. Dazu muss das MTA gelöscht oder die Telefonnummer deaktiviert werden. Info: Die Einstellung hat Einfluss auf NetworkAccess und MaxCPE im Modem Configfile - siehe Modem-Analyse im Tab \'Configfile\'',
    'ssh_auto_prov'                 => 'Periodisches Ausführen eines auf das OLT angepasstes Skript um ONTs automatisch online zu bringen.',
    'start_time' => 'Geben Sie einen gültigen Wert im Format HH:MM ein. Dies ist die Tageszeit, zu der das System beginnt zu prüfen, ob Modems ein Firmware-Upgrade benötigen.',
    'start_date' => 'Geben Sie ein gültiges Datum im Format dd/mm/yyyy ein. Dies ist das Datum, an dem das System mit der Überprüfung beginnt, ob Modems ein Firmware-Upgrade benötigen.',
    'batch_size' => 'Geben Sie eine gültige Zahl ein. Dies wird die Anzahl der Modems sein, die gleichzeitig für Firmware-Upgrades bearbeitet werden.',
    'cron_string' => 'Geben Sie einen gültigen Cron-Ausdruck im Cron-Syntax ein. Ein Cron-Ausdruck besteht aus fünf Feldern, die Minute, Stunde, Tag des Monats, Monat und Wochentag repräsentieren und durch Leerzeichen getrennt sind. Verwenden Sie die https://crontab.guru/ Website für Hilfe, Beispiele und um Ihre Cron-Ausdrücke zu validieren.',
    'finished_time' => 'Dieses Feld zeigt das Datum und die Uhrzeit an, wenn das System erkannt hat, dass keine weiteren Modems ein Firmware-Upgrade benötigen. Es wird automatisch eingestellt.',
    'firmware_match_string' => 'Jede Zeile des Textbereichs wird als separater Regex-String behandelt.',
    'restart_only' => 'Startet Geräte mit der jeweiligen Firmware nur neu.',
    /*
     * MODULE: ProvVoip
     */
    // PhonenumberController
    'Phonenumber_ActiveWithManagement' => 'Wird automatisch anhand der (De)Aktivierungsdaten in der Rufnummernverwaltung gesetzt.',
    'Phonenumber_ActiveWithoutManagement' => 'Wenn aktiviert wird die Rufnummer auf das MTA provisioniert. Wird automatisch gesetzt, wenn eine Rufnummernverwaltung angelegt wird.',
    'Phonenumber_ReassignableWithManagement' => 'Wird automatisch anhand des Deaktivierungsdatums in der Rufnummernverwaltung gesetzt.',
    'Phonenumber_ReassignableWithoutManagement' => 'Wenn aktiviert kann die Rufnummer ohne vorheriges Löschen an einem anderen MTA angelegt werden. Wird automatisch gesetzt, wenn eine Rufnummernverwaltung angelegt wird.',
    'Phonenumber_ReassignableFinal' => 'Nachdem eine Rufnummer als wieder zuweisbar markiert ist, kann dieser Zustand nicht mehr geändert werden.',
    // PhonenumberManagementController
    'PhonenumberManagement_activation_date' => 'Wird als Wunschtermin der Schaltung zum Provider geschickt und für die Ermittlung des Aktiv-Status der Rufnummer verwendet.',
    'PhonenumberManagement_deactivation_date' => 'Wird als Wunschtermin der Abschaltung zum Provider geschickt und für die Ermittlung des Aktiv-Status der Rufnummer verwendet.',
    'PhonenumberManagement_CarrierIn' => 'Bei eingehender Portierung auf vorherigen Provider setzen.',
    'PhonenumberManagement_CarrierInWithEnvia' => 'Bei eingehender Portierung auf vorherigen Provider setzen. Bei Beantragung einer neuen Rufnummer setzen Sie dieses Feld auf envia TEL.',
    'PhonenumberManagement_EkpIn' => 'Bei eingehender Portierung auf vorherigen Provider setzen.',
    'PhonenumberManagement_EkpInWithEnvia' => 'Bei eingehender Portierung auf vorherigen Provider setzen. Bei Beantragung einer neuen Rufnummer setzen Sie dieses Feld auf envia TEL.',
    'PhonenumberManagement_TRC' => 'Nur zur Info: Sperrklassenänderungen müssen beim aktuellen Provider durchgeführt werden.',
    'PhonenumberManagement_TRCWithEnvia' => 'Sperrklassenänderungen müssen auch bei envia TEL vorgenommen werden (Update VoIP account)!',
    'PhonenumberManagement_ExternalActivationDate' => 'Datum der Aktivierung beim Provider.',
    'PhonenumberManagement_ExternalActivationDateWithEnvia' => 'Datum der Aktivierung bei envia TEL.',
    'PhonenumberManagement_ExternalDeactivationDate' => 'Datum der Deaktivierung beim Provider.',
    'PhonenumberManagement_ExternalDeactivationDateWithEnvia' => 'Datum der Deaktivierung bei envia TEL.',
    'PhonenumberManagement_Autogenerated' => 'Dieses Management wurde automatisch erzeugt. Bitte sämtliche Werte überprüfen und nach evtl. Korrektur den Haken entfernen',
    /*
     * MODULE VoipMon
     */
    'mos_min_mult10'                => 'Minimaler Mean Opionion Score während des Anrufs',
    'caller'                        => 'Betrachtung der Anrufrichtung von Anrufer zu Angerufenem',
    'a_mos_f1_min_mult10'           => 'Minimaler Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 50ms',
    'a_mos_f2_min_mult10'           => 'Minimaler Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 200ms',
    'a_mos_adapt_min_mult10'        => 'Minimaler Mean Opionion Score während des Anrufs mit einem adaptiven Jitter-Buffer von 500ms',
    'a_mos_f1_mult10'               => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 50ms',
    'a_mos_f2_mult10'               => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 200ms',
    'a_mos_adapt_mult10'            => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem adaptiven Jitter-Buffer von 500ms',
    'a_sl1' => 'Anzahl der Pakete, welche einen aufeinander folgenden Paketverlust während des Anrufs aufweisen',
    'a_sl9' => 'Anzahl der Pakete, welche neun aufeinander folgende Paketverluste während des Anrufs aufweisen',
    'a_d50' => 'Anzahl der Pakete, welche eine Paketverzögerung (Packet Delay Variation - z.B. Jitter) zwischen 50ms and 70ms aufweisen',
    'a_d300' => 'Anzahl der Pakete, welche eine Paketverzögerung (Packet Delay Variation - z.B. Jitter) von über 300ms aufweisen',
    'called' => 'Betrachtung der Anrufrichtung von Angerufenem zum Anrufer',
    'mtaDomainNameForProv' => 'Hier können Sie einen separaten Domain Namen für MTA\'s angeben, falls dieser für die Provisionierung benötigt wird.',
    'delete_record_interval' => 'Anzahl der Tage, nachdem die VoipMon Anrufaufzeichnungen gelöscht werden.',
    /*
     * Module Ticketsystem
     */
    'assign_user' => 'Zuweisen eines Users zu einem Ticket.',
    'mail_env'    => 'Nächster Schritt: Host/Nutzernamen/Passwort in /etc/nmsprime/env/global.env eintragen, um Emails im Bezug auf Tickets zu erhalten.',
    'noReplyMail' => 'Die E-Mail-Adresse, die als Absender angezeigt werden soll, wenn Tickets geändert/erstellt werden. Die Adresse muss nicht existieren. Z.B. example@example.com',
    'noReplyName' => 'Der Name, der als Absender angezeigt werden soll, wenn Tickets geändert/erstellt werden. Z.B: NMS Prime',
    'ticket_settings' => 'Nächster Schritt: Den Namen und die E-Mail-Adresse des Noreply Absenders in der Systemkonfiguration unter dem Reiter Tickets angeben.',
    'carrier_out'      => 'Carriercode des zukünftigen Vertragspartners der Rufnummer. Wenn leer, wird die Rufnummer gelöscht.',
    'ticketDistance' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Distanz eines Technikers zur Störstelle. (Standard: 1)',
    'ticketModemcount' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Anzahl der betroffenen Modems. (Standard: 1)',
    'ticketOpentickets' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Anzahl der bereits zugewiesenen und in Bearbeitung befindlichen Tickets. (Standard: 1)',
    'mailLink' => 'Wenn Sie Schwierigkeiten beim Klicken auf den  \":actionText\" Button haben, kopieren Sie die nachfolgende URL in ihren Webbrowser:',

    /*
     * Start alphabetical order
     */
    'endpointMac' => 'Kann für alle PPPoE provisionierte Modems frei gelassen werden (dann wird der PPP Nutzername statt der MAC genutzt). Mit DHCP kann das Feld für IPv4 frei gelassen werden. Dann bekommen alle Geräte hinter dem Modem die spezifizierte IP, wobei nur das Gerät, dass sich zuletzt gemeldet hat, eine funktionierende IP-Konnektivität erhält. Für IPv6 ist dies noch nicht implementiert - bitte geben Sie hier immer die MAC des CPE an, das eine öffentliche oder feste IP erhalten soll!',
    'settlementrun' => [
        'rcd' => 'Das Datum an dem die Bank die Buchungen vornehmen soll. Bei Änderung wird der Rechnungslauf für alle Konten erneut ausgeführt. Dieses Datum kann für einzelne Verträge auch spefizisch im Vertrag angepasst werden.',
    ],
    'statisticsQuery' => [
        'upsell' => 'Produkt-Upsell miteinbeziehen: Bezieht bereits bestehende Kunden mit ein, welche eine weiteres Produkt (Tarifwechsel) gekauft haben.',
        'auto' => 'Die Cron-Zeichenkette zum automatischen, wiederholten Abrufen der Statistik. Format: Minute Stunde Tag Monat Wochentag. Siehe: www.crontab.guru. Entfernen Sie die Zeichenkette, wenn sie die wiederkehrende Ausführung der Abfrage stoppen möchten!',
    ],
    'statsSummary' => [
        'upsell' => 'Zählt ebenfalls bestehende Kunden, die bereits einen gültigen Tarif hatten (und im Zeitraum z.B. einen Tarifwechsel vorgenommen haben).',
    ],

    /*
     * MODULE: CRM
     */
    'crm' => [
        'contact' => [
            'type' => 'Wählen Sie, ob es sich um eine Privatperson oder eine Organisation/Firma handelt',
            'salutation' => 'Formelle Anrede oder Gruß (z.B. Herr, Frau, Dr.)',
            'firstname' => 'Vorname des privaten Kontakts',
            'lastname' => 'Nachname des privaten Kontakts',
            'company' => 'Firmen- oder Organisationsname (erforderlich für Organisationstyp)',
            'email' => 'Primäre E-Mail-Adresse für die Kommunikation',
            'phone' => 'Primäre Telefonnummer für die Kommunikation',
            'birthday' => 'Geburtsdatum (für private Kontakte)',
            'apartment' => 'Optionaler Link zu einer bestimmten Wohnung im Immobilienverwaltungssystem',
            'party_id_ext' => 'Externer Bezeichner von TMF (TeleManagement Forum) oder anderen externen Systemen',
            'notes' => 'Zusätzliche Informationen oder Kommentare zu diesem Kontakt',
        ],
        'lead' => [
            'source' => 'Wählen Sie die Quelle dieses Leads',
            'status' => 'Aktueller Status des Leads im Verkaufsprozess',
            'legal_basis' => 'Rechtliche Grundlage für die Verarbeitung der Daten dieses Leads',
            'apartment' => 'Optionaler Link zu einer bestimmten Wohnung im Immobilienverwaltungssystem',
            'owner' => 'Benutzer, der für die Verwaltung dieses Leads verantwortlich ist',
            'disqual_reason' => 'Grund, warum dieser Lead nicht qualifiziert wurde (falls zutreffend)',
            'notes' => 'Zusätzliche Informationen oder Kommentare zu diesem Lead',
        ],
        'opportunity' => [
            'contact_point_id' => 'Wählen Sie den Kontaktpunkt für diese Chance',
            'created_from_lead_id' => 'Wählen Sie den Lead aus, aus dem diese Chance erstellt wurde (optional, eindeutig)',
            'realty_id' => 'Wählen Sie die Immobilie für diese Chance (optional)',
            'apartment_id' => 'Wählen Sie die spezifische Wohnung für diese Chance (optional)',
            'pipeline_id' => 'Wählen Sie den Verkaufspipeline für diese Chance',
            'stage_id' => 'Wählen Sie die aktuelle Stufe im Pipeline',
            'amount_cents' => 'Geben Sie den Chancenbetrag in Cent ein (z.B. 10000 für €100,00)',
            'deal_size' => 'Geben Sie die Geschäftsgröße in Cent ein (z.B. 10000 für €100,00)',
            'probability_pct' => 'Geben Sie den Wahrscheinlichkeitsprozentsatz ein (0-100)',
            'expected_close_date' => 'Wählen Sie das erwartete Abschlussdatum für diese Chance',
            'is_preorder' => 'Markieren Sie, wenn es sich um eine Vorbestellungs-Chance handelt',
            'is_switcher' => 'Markieren Sie, wenn es sich um einen Kunden handelt, der von einem anderen Anbieter wechselt',
            'external_order_no' => 'Geben Sie die externe Bestellnummer ein, falls zutreffend',
            'precheck_result' => 'Geben Sie Vorprüfungsergebnisse als JSON ein (optional)',
            'deal_terms_json' => 'Geben Sie Deal-Bedingungen als JSON ein (optional)',
            'porting_requested_at' => 'Wählen Sie aus, wann die Portierung angefordert wurde (optional)',
            'porting_date' => 'Wählen Sie das tatsächliche Portierungsdatum (optional)',
        ],
        'pipeline' => [
            'name' => 'Anzeigename für die Pipeline',
            'is_default' => 'Markieren Sie diese Pipeline als Standard für neue Chancen',
            'stages' => 'Verwalten Sie die Stufen, die diesen Pipeline-Workflow bilden',
        ],
        'pipelineStage' => [
            'pipelineId' => 'Wählen Sie die Pipeline aus, zu der diese Stufe gehört',
            'name' => 'Anzeigename für die Stufe',
            'orderIndex' => 'Position dieser Stufe in der Pipeline-Sequenz (0-basiert)',
            'defaultProbabilityPct' => 'Standard-Wahrscheinlichkeitsprozentsatz für Chancen in dieser Stufe (0-100)',
            'color' => 'Farbcode für die UI-Anzeige (z.B. "#FF0000" oder "rot")',
            'isTerminal' => 'Markieren Sie, wenn dies eine Endstufe ist, die den Pipeline-Fluss stoppt',
            'isWon' => 'Markieren Sie, wenn diese Stufe eine gewonnene Chance darstellt',
            'isLost' => 'Markieren Sie, wenn diese Stufe eine verlorene Chance darstellt',
        ],
        'stage_transition' => [
            'pipeline_id' => 'Wählen Sie die Pipeline aus, zu der dieser Übergang gehört',
            'from_stage_id' => 'Wählen Sie die Startstufe für diesen Übergang',
            'to_stage_id' => 'Wählen Sie die Zielstufe für diesen Übergang',
            'guard_expr' => 'JSON-Ausdruck, der zu true ausgewertet werden muss, damit dieser Übergang erlaubt ist (optional)',
            'autofail_message' => 'Nachricht, die angezeigt wird, wenn dieser Übergang die Validierung nicht besteht (optional)',
        ],
    ],

    /*
     * MODULE: ContactBase (Global - same as contact_point for consistency)
     */
    'contact' => [
        'type' => 'Wählen Sie, ob es sich um eine Privatperson oder eine Organisation/Firma handelt',
        'salutation' => 'Formelle Anrede oder Gruß (z.B. Herr, Frau, Dr.)',
        'firstname' => 'Vorname des privaten Kontakts',
        'lastname' => 'Nachname des privaten Kontakts',
        'company' => 'Firmen- oder Organisationsname (erforderlich für Organisationstyp)',
        'email' => 'Primäre E-Mail-Adresse für die Kommunikation',
        'phone' => 'Primäre Telefonnummer für die Kommunikation',
        'birthday' => 'Geburtsdatum (für private Kontakte)',
        'apartment' => 'Optionaler Link zu einer bestimmten Wohnung im Immobilienverwaltungssystem',
        'party_id_ext' => 'Externer Bezeichner von TMF (TeleManagement Forum) oder anderen externen Systemen',
        'notes' => 'Zusätzliche Informationen oder Kommentare zu diesem Kontakt',
    ],

    /*
     * MODULE: Address (Global)
     */
    'address' => [
        'street' => 'Straßenname und -nummer',
        'house_number' => 'Haus- oder Gebäudenummer',
        'zip' => 'Postleitzahl',
        'city' => 'Stadt- oder Ortsname',
        'district' => 'Bezirk oder Stadtteil',
        'source' => 'Quelle der Adressdaten (z.B. manueller Eintrag, Geocodierung)',
        'lat' => 'Geografische Breitenkoordinate',
        'lng' => 'Geografische Längenkoordinate',
    ],

    /*
     * CRM Opportunity Helper Texts
     */
    'amount_cents_help' => 'Geben Sie den Chancenbetrag in Cent ein (z.B. 10000 für €100,00)',
    'deal_size_help' => 'Geben Sie die Geschäftsgröße in Cent ein (z.B. 10000 für :currency100,00)',
    'probability_pct_help' => 'Geben Sie den Wahrscheinlichkeitsprozentsatz ein (0-100)',
    'please_select_pipeline_first' => 'Bitte wählen Sie zuerst eine Pipeline aus, um verfügbare Stufen zu sehen',

    /*
     * MODULE: CustomerInteraction (Global)
     */
    'ci_channel' => [
        'created' => 'Kundeninteraktions-Kanal wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Kanal wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Kanal wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Kanal nicht gefunden.',
    ],
    'ci_direction' => [
        'created' => 'Kundeninteraktions-Richtung wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Richtung wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Richtung wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Richtung nicht gefunden.',
    ],
    'ci_status' => [
        'created' => 'Kundeninteraktions-Status wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Status wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Status wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Status nicht gefunden.',
    ],
    'ci_category' => [
        'created' => 'Kundeninteraktions-Kategorie wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Kategorie wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Kategorie wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Kategorie nicht gefunden.',
    ],
    'ci_field' => [
        'created' => 'Kundeninteraktions-Feld wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Feld wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Feld wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Feld nicht gefunden.',
    ],
    'ci_requirement_level' => [
        'created' => 'Kundeninteraktions-Anforderungsstufe wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Anforderungsstufe wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Anforderungsstufe wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Anforderungsstufe nicht gefunden.',
    ],
    'ci_category_field_rule' => [
        'created' => 'Kundeninteraktions-Kategorie-Feld-Regel wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktions-Kategorie-Feld-Regel wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktions-Kategorie-Feld-Regel wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktions-Kategorie-Feld-Regel nicht gefunden.',
    ],
    'ci_customer_interaction' => [
        'created' => 'Kundeninteraktion wurde erfolgreich erstellt.',
        'updated' => 'Kundeninteraktion wurde erfolgreich aktualisiert.',
        'deleted' => 'Kundeninteraktion wurde erfolgreich gelöscht.',
        'not_found' => 'Kundeninteraktion nicht gefunden.',
    ],
];
