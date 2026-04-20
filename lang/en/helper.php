<?php

return [
    'ccc' => [
        'imgUpload' => 'Upload for logo, backgound image and home banner. Please set the according image after the upload!',
        'passwordResetEmailSent' => 'Email sent.',
        'passwordResetInvalid' => 'This password reset link is invalid or has expired. Please request a new one.',
        'passwordResetSuccess' => 'Your password was updated. You can sign in now.',
    ],
    'dateFormat' => 'Y-m-d',
    'dateTimeFormat' => 'Y-m-d H:i:s',
    'debt' => [
        'amount' => 'Postive when customer is charged, negative when customer gets credit',
        'debtToClear' => 'Here only debts are shown of which the absolute remaining amount is larger then the remaining amount of this transaction.',
        'totalFee' => 'Deprecated. Just used to show the old total fee that is now determined by the sum of bank and extra fee. The field will probably be removed in future to reduce redundant informations.',
    ],
    'rkmServerAddress' => 'IP:Port (IP can also be a hostname). If you set the global server infos we will try to set all taps & tap ports automatically via this server. If you have more than one RKM-Server please add netelements of type RKM-Server. Then the corresponding server will be determined by the parent relations in the netelement / ERD.',

    /*
     * Authentication and Base
     */
    'translate'                 => 'You can help translating NMS PRIME at',
    'assign_role'                   => 'Assign one or more Roles to this User. Users without a Role cant use the NMS because they got no Permissions.',
    'assign_users'                  => 'Assign one or more Users to this Role. Changes made here are not visible in the GuiLog of the user.',
    'assign_rank'                   => 'The rank of a role determines the ability to edit other users. <br \>You can assign values from 0 to 100. (higher is better). <br \>If a user has more than one role, the highest rank is used. <br \>If the ability to update users is set, the rank is also checked. Only if the rank of the editor is higher, permission is granted. Furthermore, when creating or updating users, only roles with equal or lower rank can be assigned.',
    'All abilities'                 => 'This ability allows all authorisation requests, except for the abilities, which are explicitly forbidden. This is mainly a helper ability. Forbidding is disabled, because only checked Abilities are allowed. If this Ability is not checked, you have to set all abilities by hand. If you change this ability, when many other abilities are set, it will take up to 1 minute to apply all the changes.',
    'countryCode'               => 'ISO 3166 ALPHA-2 (two characters). Needed for determination of geocodes. If empty the globally specified default country code is used.',
    'View everything'           => 'This ability allows to view all pages. Forbidding is disabled, because it makes the NMS unusable. This is mainly a helper ability for guests or very low priviledged users.',
    'Use api'                   => 'This ability allows or forbids to access the API routes with "Basic Auth" (the email is used as username).',
    'See income chart'          => 'This ability allows or forbids to view the income chart on the dashboard.',
    'View analysis pages of modems' => 'This ability allows or forbids to access the analysis pages of a modem.',
    'View analysis pages of netgw' => 'This ability allows or forbids to access the analysis pages of a NetGw.',
    'Download settlement runs'  => 'This ability allows or forbids the download of settlement runs. This ability has no impact if it is forbidden to manage settlement runs.',
    /*
     * Index Page - Datatables
     */
    'SortSearchColumn'              => 'This Column cannot be searched or ordered.',
    'PrintVisibleTable'             => 'Prints the shown table. If the table is filtered make sure to select the \\"All\\" option to display everything. Loading can take a few seconds.',
    'ExportVisibleTable'            => 'Exports the shown table. If the table is filtered make sure to select the \\"All\\" option to display everything. Loading can take a few seconds.',
    'ChangeVisibilityTable'         => 'Select the columns that should be visible.',
    'clearFilter'                   => 'Clear column and table search filter.',

    // GlobalConfig
    'ISO_3166_ALPHA-2'              => 'ISO 3166 ALPHA-2 (two characters, e.g. “US”). Used in address forms to specify the country.',
    'PasswordReset'                 => 'This property defines the timespan in days in which the users of the administration panel should change their passwords. If you want to disable the password reset message, set the value to 0.',
    'syncProvision'                 => 'TR-069 only: This property specifies if there is another button on the modem edit page, that synchronizes the modem with it\'s correlated configfile. This allows to update the modem (e.g. after adding a new phonenumber) without resetting it.',

    // CompanyController
    'Company_Management'            => 'Comma separated list of names',
    'Company_Directorate'           => 'Comma separated list of names',
    'Company_TransferReason'        => 'Template from all Invoice class data field keys - Contract Number and Invoice Nr is default',
    'conn_info_template'            => 'Tex Template used to Create Connection Information on the Contract Page for a Customer',

    // ProductRegionRule helper translations
    'product_region_rule' => [
        'scope_ref_id' => 'Reference ID for the selected scope type (DMA ID, City ID, Street ID, etc.)',
        'requires_right_code' => 'Optional: Code of a specific user right required to access this product',
        'effective_from' => 'Optional: Start date when this rule becomes active',
        'effective_to' => 'Optional: End date when this rule expires',
        'priority' => 'Priority order for rule evaluation (lower numbers = higher priority)',
    ],

    // ProductLayer helper translations
    'product_layer' => [
        'sequence' => 'Display order for layers (lower numbers appear first)',
        'logo' => 'Optional image for the customer shop card. When set, it overrides product logos for this layer.',
        'logo_upload' => 'Upload a new file; it is stored for this product layer and can be selected as the shop card logo.',
    ],

    // ProductLayerAssignment helper translations
    'product_layer_assignment' => [
        'sort' => 'Sort order within the layer (lower numbers appear first)',
    ],

    // ProductConstraint helper translations
    'product_constraint' => [
        'min_qty' => 'Minimum quantity required when enforcement is for product type',
    ],

    // ProductAddon helper translations
    'product_addon' => [
        'base_product_id_or_layer' => 'Either select a specific product OR specify a product layer',
        'product_layer_id_explanation' => 'Product layer this addon applies to (addons show after layer completion)',
        'max_qty' => 'Maximum quantity of this addon that can be selected',
        'sort' => 'Display order for this addon (lower numbers appear first)',
    ],

    // Order Portal Config
    'order_portal_config' => [
        'primary_color' => 'Hex color code for primary theme color (e.g., #007bff). Used for buttons, links, and key UI elements in the web order portal.',
        'secondary_color' => 'Hex color code for secondary theme color (e.g., #6c757d). Used for secondary UI elements and backgrounds.',
        'inverted_product_color_boxes' => 'When enabled, product boxes in the order process (CCC and WebOrder) will use inverted colors: secondary color for selected products and primary color for secondary elements.',
        'show_header' => 'Display the header/navigation bar in the customer web order portal. Uncheck to hide the header for embedded/iframe scenarios.',
        'enable_business_customers' => 'When enabled, customers can choose between residential and business customer types during the order process. When disabled, this option is hidden.',
        'allow_plan_selection_none' => 'When enabled, each product layer in plan selection can include an explicit "None" choice (no product in that layer). When disabled, customers must pick a real product for every layer that offers products.',
        'payment_method_sepa' => 'Enable SEPA direct debit as an available payment method for customers.',
        'payment_method_rechnung' => 'Enable invoice (bank transfer) as an available payment method for customers.',
        'payment_method_acs' => 'Enable ACS (Automated Clearing Service) as an available payment method for customers.',
        'payment_method_credit_card' => 'Enable credit card payment as an available payment method for customers.',
        'postal_invoice_product_id' => 'Select the product to use for postal invoices. This should be a product of type "Postal". Used in both WebOrder and CCC for invoice delivery.',
        'transfer_phone_product_id' => 'Select the product to use for telephone number porting. When this product is selected during weborder or CCC upgrade, customers will be asked to provide porting information.',
        'weborder_legal_urls' => 'One line per checkbox. Format: "Name: URL". Each line will be displayed as a checkbox that customers must accept before completing their order. Example: Terms & Conditions: https://example.com/terms/',
        'cache_notice_title' => 'IMPORTANT: Data is cached',
        'cache_notice' => 'Configuration data is cached for performance. After making changes, please clear the cache using the following command:',
    ],
    'cccConfig' => [
        'cache_notice_title' => 'IMPORTANT: Data is cached',
        'cache_notice' => 'Configuration data is cached for performance. After making changes, please clear the cache using the following command:',
        'block_internet_downselling' => 'Prevents customers in the CCC portal from selecting a slower internet plan than their current plan. Default: enabled.',
        'allow_modem_address_switching' => 'Allows customers in the CCC portal to change their modem address. When disabled, the modem address menu item will be hidden. Default: disabled.',
        'payment_method_sepa' => 'Enable SEPA direct debit as an available payment method for customers in the CCC portal.',
        'payment_method_rechnung' => 'Enable invoice (bank transfer) as an available payment method for customers in the CCC portal.',
        'payment_method_acs' => 'Enable ACS (Automated Clearing Service) as an available payment method for customers in the CCC portal.',
        'payment_method_credit_card' => 'Enable credit card payment as an available payment method for customers in the CCC portal.',
        'postal_invoice_product_id' => 'Select the product to use for postal invoices. This should be a product of type "Postal".',
        'transfer_phone_product_id' => 'Select the product to use for telephone number porting. When this product is selected during CCC upgrade, customers will be asked to provide porting information.',
        'customer_ticket_type_parent' => 'Select the parent ticket type that will be used as the root for customer change requests in the CCC portal. Customers will be able to select from ticket types that are children of this parent.',
        'invoice_address_change_ticket_type_id' => 'If set, CCC customers cannot change the invoice address directly; submitting the form creates a ticket of this type with the requested data. Leave empty to allow immediate updates.',
        'modem_address_change_ticket_type_id' => 'If set, CCC customers cannot change the modem address directly; submitting the form creates a ticket of this type with the requested data. Leave empty to allow immediate updates.',
        'homeButtonName' => 'Label for the home button in the CCC navigation (left menu). Leave empty to remove the menu entry.',
        'welcomeMessage' => 'Welcome text shown on the CCC home page below the welcome heading.',
        'manuals_url' => 'Full URL (https://…) of the customer manuals page. If empty, the manuals menu entry and home card are hidden.',
    ],

    // Web Order
    'web_order' => [
        'order_number_help' => 'Short, user-friendly order number generated from the hash for display purposes.',
        'availability_snapshot_help' => 'JSON data containing availability check results and service options for the customer address.',
        'utm_json_help' => 'JSON data containing UTM parameters and tracking information from the web order source.',
        'consent_json_help' => 'JSON data containing customer consent information and GDPR compliance data.',
        'payment_data_help' => 'JSON data containing encrypted payment information (IBAN, credit card details, etc.) for verification purposes.',
    ],

    // CostCenterController
    'CostCenter_BillingMonth'       => 'Accounting for yearly charged items - corresponds to the month the invoices are created for. Default: 6 (June) - if not set. Please be careful to not miss any payments when changing this!',

    // ItemController
    'item' => [
        'productId'                => 'All fields besides Billing Cycle have to be cleared before a type change! Otherwise items can not be saved in most cases',
        'validFrom'                => 'For One Time Payments the fields can be used to split payment - Only YYYY-MM is considered then!',
        'validFromFixed'           => 'Checked by default! Uncheck if the tariff shall stay inactive when start date is reached (e.g. if customer is waiting for a phone number porting). The tariff will not start and not be charged until you activate the checkbox. Further the start date will be incremented every day by one day after reaching the start date. Info: The date is not updated by external orders (e.g. from telephony provider).',
        'validTo'                  => 'It\'s possible to specify the number of months here - e.g. \'12M\' for 12 months. For monthly paid products it will just add the number of months - so start date 2018-05-04 will be valid to 2019-05-04. Single paid items with splitted payment will be charged 12 times - end date will be 2019-04-31 then.',
        'validToFixed'             => 'Checked by default! Uncheck if the end date is uncertain. If unchecked the tariff will not end and will be charged until you activate the checkbox. Further when the end date is reached it will be incremented every day by one day. Info: The date is not updated by external orders (e.g. from telephony provider).',
        'creditAmount'             => 'Overwrites the price of the corresponding product. For Credits: Net Amount to be credited to Customer. Take Care: a negative amount becomes a debit! (on credits)',
        'accountingText'           => 'If set, this text is used as the invoice and settlement line label instead of the product name. Leave empty to use the product name.',
    ],

    'crmOpportunityItem' => [
        'productId' => 'Product the opportunity item is assigned to. Select the appropriate product for this CRM opportunity.',
        'validFrom' => 'Date from when the opportunity item should be valid.',
        'validFromFixed' => 'If checked the date is fixed and will not be changed.',
        'validTo' => 'Date until when the opportunity item should be valid. You can use format 12M for 12 months from valid from date.',
        'validToFixed' => 'If checked the date is fixed and will not be changed.',
        'creditAmount' => 'If set this amount will be used instead of the product price for this opportunity item.',
    ],

    // ProductController
    'product' => [
        'billingCycles' => 'You can find the explanations to the billing/payroll cycles in the official documentation (web) -> Enterprise Applications -> Prime Billing -> Products',
        'bundle'                => 'On bundled tarifs the minimum runtime of the contract is determined only be the internet tariff. Otherwise the last starting valid tariff (Voip or Internet) dictates this date.',
        'markon'                => 'Additional charge to call data records. This percentual extra charge is currently only added to Opennumbers CDRs.',
        'maturity_min'          => 'Tariff minimum period/runtime/term. E.g. 14D (14 days), 3M (three months), 1Y (one year)',
        'maturity'              => 'Tariff period/runtime/term extension after the minimum runtime. <br> Will be automatically added when tariff was not canceled before period of notice. Default 1 month. If no maturity is given the end of term of the item is always set to the last day of the month. <br><br> E.g. 14D (14 days), 3M (three months), 1Y (one year)',
        'Name'                  => 'For Credits it is possible to assign a Type by adding the type name to the Name of the Credit - e.g.: \'Credit Device\'',
        'parentId' => 'Please chose the basic tariff here that this product builds a combination tariff with. Currently this is only used to create the statistic reports and has no further functionality.',
        'pod'                   => 'E.g. 14D (14 days), 3M (three months), 1Y (one year)',
        'proportional'          => 'Activate this checkbox when items that begin during the current settlement run shall be charged proportionately. E.g. if an monthly paid item starts in the middle of the month the customer would be charged only half of the full price in this settlement run.',
        'Type'                  => 'All fields besides Billing Cycle have to be cleared before a type change! Otherwise products can not be saved in most cases',
        'deprecated'            => 'Activate this checkbox if this product shall not be shown in the product select list when creating/editing items.',
        'description_html'      => 'Enter HTML or RTF formatted content for product description. You can include links, images, and formatted text. This content will be displayed when viewing the product.',
        'logo'                  => 'Select a logo image for this product. The logo will be displayed in the OrderPortal plan selection page instead of the default icon.',
        'logo_upload'           => 'Upload a new logo image file. Supported formats: JPG, PNG, GIF. The uploaded file will be available for selection.',
    ],
    'Product_Number_of_Cycles'      => 'Take Care!: for all repeatedly payed products the price stands for every charge, for Once payed products the Price is divided by the number of cycles',

    // SalesmanController
    'Salesman_ProductList'          => 'Add all Product types he gets commission for - possible: ',

    // SepaMandate
    'sm_cc'                         => 'If a cost center is assigned only products related to the same cost center will be charged of this account. Leave this field empty if all charges that can not be assigned to another SEPA-Mandate with specific cost center shall be debited of this account. Note: It is assumed that all emerging costs that can not be assigned to any SEPA-Mandate will be payed in cash!',
    'sm_recur'                      => 'Activate if there already have been transactions of this account before the creation of this mandate. Sets the status to recurring. Note: This flag is only considered on first transaction!',

    // SettlementrunController
    'settlement_verification'       => 'Customer Invoices are only visible when this checkbox is activated. The checkbox can only be activated if the last run was executed for ALL SEPA accounts (to not miss any changes). Info: If activated it\'s not possible to repeat the Settlement Run.',

    /*
     * MODULE: Dashboard
     */
    'next'                          => 'Next step: ',
    'set_isp_name'                  => 'Set internet service provider name',
    'create_netgw'                  => 'Create first NetGw/CMTS',
    'create_cm_pool'                => 'Create first cablemodem IP pool',
    'create_cpepriv_pool'           => 'Create first private CPE IP pool',
    'create_qos'                    => 'Create first QoS profile',
    'create_product'                => 'Create first billing product',
    'create_configfile'             => 'Create first configfile',
    'create_sepa_account'           => 'Create first SEPA account',
    'create_cost_center'            => 'Create first cost center',
    'create_contract'               => 'Create first contract',
    'create_nominatim'              => 'Set an email address (OSM_NOMINATIM_EMAIL) in /etc/nmsprime/env/global.env to enable geocoding for modems',
    'create_nameserver'             => 'Set your nameserver to 127.0.0.1 in /etc/resolv.conf and make sure it won\'t be overwritten via DHCP (see DNS and PEERDNS in /etc/sysconfig/network-scripts/ifcfg-*)',
    'create_modem'                  => 'Create first modem',

    /*
     * MODULE: HfcReq
     */
    'netelement' => [
        'credentials' => 'Currently only used for netelements of type \'RKM-Server\' to connect to the server during the change of settings of taps',
    ],
    'netelementtype' => [
        'reload'        => 'In Seconds. Zero to deactivate autoreload. Decimals possible.',
        'sidebarPos'    => 'Position in the sidebar - descending. The netelement type with the highest number will be placed at the bottom of the sidebar. Please let the field empty if the netelement type shall not appear in the sidebar.',
        'time_offset'   => 'In Seconds. Decimals possible.',
        'undeleteables' => 'Net & Cluster can not be changed due to there relevance for all the Entity Relation Diagrams',
    ],
    'gpsUpload'                     => 'Has to be a GPS file of type WKT, EWKT, WKB, EWKB, GeoJSON, KML, GPX or GeoRSS',

    /*
     * MODULE: HfcSnmp
     */
    'mib_filename'                  => 'The Filename is composed by MIB name & Revision. If there is already an existent identical File it\'s not possible to create it again.',
    'oid_link'                      => 'Go to OID Settings',
    'oid_table'                     => 'INFO: This Parameter belongs to a Table-OID. If you add/specify SubOIDs or/and indices, only these are considered for the snmpwalk. Besides the better Overview this can dramatically speed up the Creation of the Controlling View for the corresponding NetElement.',
    'parameter_3rd_dimension'       => 'Check this box if this Parameter belongs to an extra Controlling View behind an Element of the SnmpTable.',
    'parameter_diff'                => 'Check this if only the Difference of actual to last queried values shall be shown.',
    'parameter_divide_by'           => 'Make this ParameterValue percentual compared to the added values of the following OIDs that are queried by the actual snmpwalk, too. In a first step this only works on SubOIDs of exactly specified tables! The Calculation is also done after the Difference is calculated in case of Difference-Parameters.',
    'parameter_indices'             => 'Specify a comma separated List of all Table Rows we want the Snmp Values for.',
    'parameter_html_frame'          => 'Assign this parameter to a specific frame (part of the page). Doesn\'t have influences on SubOIDs in Tables (but on 3rd Dimensional Params!).',
    'parameter_html_id'             => 'By adding an ID you can order this parameter in sequence to other parameters. In tables you can change the column order by setting the Sub-Params html id.',

    /*
     * MODULE: ProvBase
     */
    'contract' => [
        'lastAmendment' => 'Day when last change to contract regarding tariffs was done. This influences the contract confirmation. Only tariffs added after this date will appear on the PDF and will be considered to determine periods.',
        'number' => 'Attention - Customer login password is changed automatically on changing this field!',
        'salutation' => 'To change the options please change the content of the file(s) '.storage_path('app/config/provbase/formoptions/salutations_').'{person,institution}.txt!',
        'valueDate' => 'Day of month for specific date of value. Overrides the requested collection date from global config for this contract in the SEPA XML.',
        'salesTaxOrg' => 'Checking this box results in an invoice where no taxes are considered / calculated.',
        'shortenCdr' => 'When enabled, last 3 digits of target phonenumbers on CDR (of the invoice) are replaced by "xxx" for this contract.',
    ],
    'rate_coefficient'              => 'MaxRateSustained will be multiplied by this value to grant the user more (> 1.0) throughput than subscribed.',
    'additional_modem_reset'        => 'Check if an additional button should be displayed, which resets the modem via SNMP without querying the NetGw.',
    'auto_factory_reset'            => 'Performs an automatic factory reset for TR-069 CPEs, if relevant configurations have been changed, which reqiure a reprovision. (i.e. change of phonenumbers, PPPoE credentials or configfile)',
    'factory_reset_discovered_cpes' => 'Performs an automatic factory reset for newly discovered TR-069 CPEs',
    'use_radius_relay_info' => 'Authenticate IPoE clients via DHCP Relay Information instead of PPPoE credentials, (set sql_user_name = "%{Agent-Circuit-Id}" in /etc/raddb/mods-config/sql/main/postgresql/queries.conf)',
    'use_framed_pool' => 'Let BRAS handle IP assignments. Signal from which IP pool (named CPEPriv or CPEPub) the BRAS should assign the IP address via the Framed-Pool RADIUS attribute.',
    'global_ont' => 'Provision an ONT independent of its connectivity (OLT and port), Altiplano: fiber name not required.',
    'acct_interim_interval'         => 'The number of seconds between each interim update to be sent from the NAS for a session (PPPoE).',
    'openning_new_tab_for_modem' => 'Check the box to open the modem edit page in new tab in topography view.',
    'ppp_session_timeout'           => 'In seconds. PPP session will not be terminated when setting the value to zero.',
    'max_cpe' => 'Minimum & default: 2. A value of -1 will remove “lease limit” from DHCP config and set “MaxCPE” in DOCSIS config files to 254.',
    // ModemController
    'modem' => [
        'internetAccess' => 'Internet Access for CPEs. (MTAs are not considered and will always go online when all other configurations are correct). Take care: With Billing-Module this checkbox will be overwritten by daily check if tariff changes. Can not be set anymore when contract was canceled.',
        'qosCount' => 'The number in brackets indicates how often the respective QOS is already used.',
        'configfileSelect' => 'It\'s not possible to change the device type of a modem via configfile (e.g. from \'cm\' to \'tr-69\'). Therefore please delete the modem and create a new one!',
        'additional' => 'This field can be used to make notes.',
    ],
    'Modem_InstallationAddressChangeDate'   => 'In case of (physical) relocation of the modem: Add startdate for the new address here. If readonly there is a pending address change order at envia TEL.',
    'Modem_GeocodeOrigin'           => 'Where does geocode data come from? If set to “n/a” address could not be geocoded against any API. Will be set to your name on manually changed geodata.',
    'netGwSupportState' => [
        'full-support' => 'More than 95% of netGw modules are listed as supported devices.',
        'restricted' => 'Between 80%-95% of netGw modules are listed as supported devices.',
        'not-supported' => 'Less than 80% of netGw modules are listed as supported devices.',
        'verifying' => 'Less than 80% of netGw modules are listed as supported devices, but the netGw is still within the verification period of 6 weeks',
    ],
    'mac_formats'                   => "Allowed formats (case-insensitive):\n\n1) AA:BB:CC:DD:EE:FF\n2) AABB.CCDD.EEFF\n3) AABBCCDDEEFF",
    'fixed_ip_warning'              => 'Using fixed IP address is highly discouraged, as this breaks the ability to move modems and their CPEs freely among NetGws. Instead of telling the customer a fixed IP address they should be supplied with the hostname, which will not change.',
    'addReverse'                    => 'To set an additional reverse DNS record, e.g. for e-mail servers',
    'modem_update_frequency'        => 'This field is updated once a day.',
    'modemSupportState' => [
        'full-support' => 'The modem is listed as a supported device.',
        'not-supported' => 'The modem is not listed as a supported device.',
        'verifying' => 'The modem is not yet found as a supported device, but still within the verification period of 6 weeks.',
    ],
    'enable_agc'                    => 'Enable upstream automatic gain control.',
    'agc_offset'                    => 'Upstream automatic gain control offset in dB. (default: 0.0)',
    'configfile_count'              => 'The number in brackets indicates how often the respective configurationfile is already used.',
    'has_telephony'                 => 'Activate if customer shall have telephony but has no internet. This flag can actually not be used to disable telephony on contracts with internet. Please delete the MTA or disable the phonenumber for that. Info: The setting influences the modems configfile parameters NetworkAcess and MaxCPE - see modems analyses page tab \'Configfile\'',
    'ssh_auto_prov'                 => 'Periodically run a script tailored to the OLT in order to automatically bring ONTs online.',
    'start_time' => 'Enter a valid value in HH:MM format. This will be the time of day when the system will start to check for modems requiring a firmware upgrade.',
    'start_date' => 'Enter a valid date in dd/mm/yyyy format. This will be the date when the system will start to check for modems requiring a firmware upgrade.',
    'batch_size' => 'Enter a valid number. This will be the number of modems that will be processed for firmware upgrades at a time.',
    'cron_string' => 'Enter a valid cron string using the cron syntax. A cron string consists of five fields representing minute, hour, day of month, month, and day of week, separated by spaces. Refer to the https://crontab.guru/ website for help, examples, and to validate your cron expressions.',
    'finished_time' => 'This field shows the date and time when the system detected that no further modems require a firmware upgrade. It is set automatically.',
    'firmware_match_string' => 'Each line of the textarea will be treated as a separate regex string.',
    'restart_only' => 'Only restart devices with corresponding firmware.',
    /*
     * MODULE: ProvVoip
     */
    // PhonenumberController
    'Phonenumber_ActiveWithManagement' => 'Automatically set by (de)activation date in phonenumber management',
    'Phonenumber_ActiveWithoutManagement' => 'If set the phonenumber will be provisioned to the MTA. With an existing PhonenumberManagement this checkbox will be set depending on its (de)activation date.',
    'Phonenumber_ReassignableWithManagement' => 'Automatically set by deactivation date in phonenumber management',
    'Phonenumber_ReassignableWithoutManagement' => 'If set the phonenumber can be used at another MTA without the need to delete it first. With an existing PhonenumberManagement this checkbox will be set depending on its deactivation date.',
    'Phonenumber_ReassignableFinal' => 'Once a phonenumber is flagged as reassignable that cannot be reverted.',
    // PhonenumberManagementController
    'PhonenumberManagement_activation_date' => 'Will be sent to provider as desired date, triggers active state of the phonenumber.',
    'PhonenumberManagement_deactivation_date' => 'Will be sent to provider as desired date, triggers active state of the phonenumber.',
    'PhonenumberManagement_CarrierIn' => 'On incoming porting: set to previous Telco.',
    'PhonenumberManagement_CarrierInWithEnvia' => 'On incoming porting: set to previous Telco. In case of a new number set this to envia TEL',
    'PhonenumberManagement_EkpIn' => 'On incoming porting: set to previous Telco.',
    'PhonenumberManagement_EkpInWithEnvia' => 'On incoming porting: set to previous Telco. In case of a new number set this to envia TEL',
    'PhonenumberManagement_TRC' => 'This is for information only. Real changes have to be performed at your Telco.',
    'PhonenumberManagement_TRCWithEnvia' => 'If changed here this has to be sent to envia TEL, too (Update VoIP account).',
    'PhonenumberManagement_ExternalActivationDate' => 'Date of activation at your provider.',
    'PhonenumberManagement_ExternalActivationDateWithEnvia' => 'Date of activation at envia TEL.',
    'PhonenumberManagement_ExternalDeactivationDate' => 'Date of deactivation at envia TEL.',
    'PhonenumberManagement_ExternalDeactivationDateWithEnvia' => 'Date of deactivation at envia TEL.',
    'PhonenumberManagement_Autogenerated' => 'This management has been created automatically. Please verify/change values, then uncheck this box.',
    /*
     * MODULE VoipMon
     */
    'mos_min_mult10'                => 'Minimal Mean Opionion Score experienced during call',
    'caller'                        => 'Call direction from Caller to Callee',
    'a_mos_f1_min_mult10'           => 'Minimal Mean Opionion Score experienced during call for a fixed jitter buffer of 50ms',
    'a_mos_f2_min_mult10'           => 'Minimal Mean Opionion Score experienced during call for a fixed jitter buffer of 200ms',
    'a_mos_adapt_min_mult10'        => 'Minimal Mean Opionion Score experienced during call for an adaptive jitter buffer of 500ms',
    'a_mos_f1_mult10'               => 'Average Mean Opionion Score experienced during call for a fixed jitter buffer of 50ms',
    'a_mos_f2_mult10'               => 'Average Mean Opionion Score experienced during call for a fixed jitter buffer of 200ms',
    'a_mos_adapt_mult10'            => 'Average Mean Opionion Score experienced during call for an adaptive jitter buffer of 500ms',
    'a_sl1' => 'Number of packets experiencing one consecutive packet loss during call',
    'a_sl9' => 'Number of packets experiencing nine consecutive packet losses during call',
    'a_d50' => 'Number of packets experiencing a packet delay variation (i.e. jitter) between 50ms and 70ms',
    'a_d300' => 'Number of packets experiencing a packet delay variation (i.e. jitter) greater than 300ms',
    'called' => 'Call direction from Callee to Caller',
    'mtaDomainNameForProv' => 'Specify a Domain name here, if MTA\'s need a separate Domain for Provisioning.',
    'delete_record_interval' => 'Specify how many days the call monitoring records should be stored.',
    /*
     * Module Ticketsystem
     */
    'assign_user' => 'Allowed to assign an user to a ticket.',
    'mail_env'    => 'Next: Set your Host/Username/Password in /etc/nmsprime/env/global.env to enable receiving Emails concerning Tickets',
    'noReplyMail' => 'The E-mail address which should be displayed as the sender, while creating/editing tickets. This address does not have to exist. For example: example@example.com',
    'noReplyName' => 'The name which should be displayed as the sender, while creating/editing tickets. For example: NMS Prime',
    'ticket_settings' => 'Next: Set noreply name and address in Global Ticket Config Page.',
    'carrier_out'      => 'Carrier code of the future contractual partner. If left blank the phonenumber will be deleted.',
    'ticketDistance' => 'Multiplier for the auto ticket assignment. The higher the value, the more important the distance factor becomes. (default: 1)',
    'ticketModemcount' => 'Multiplier for the auto ticket assignment. The higher the value, the more important the affected Modem count becomes. (default: 1)',
    'ticketOpentickets' => 'Multiplier for the auto ticket assignment. The higher the value, the more important the number of new and open Tickets for technicians becomes. (default: 1)',
    'mailLink' => "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\ninto your web browser:",

    /*
     * Start alphabetical order
     */
    'endpointMac' => 'Can be left empty for all PPPoE provisioned modems (PPP username is used instead of MAC). With DHCP it can be left empty for IPv4. Then all devices behind the modem will get the specified IP, but only the last one that requested the IP will have a working IP connectivity. This is not yet implemented for IPv6 - please always specify the CPE MAC that shall get the public or fixed IP.',
    'settlementrun' => [
        'rcd' => 'Date the bookings shall be made by the bank. Changing this let\'s settlement rerun. This date can also be overwritten for specific contracts on the contract edit page.',
    ],
    'statisticsQuery' => [
        'upsell' => 'Include product upsell: Includes existing customers who have purchased another product/tariff change',
        'auto' => 'The cron string to automatically retrieve the statistic repetetive. Format: minute hour day month weekday. Refer to: www.crontab.guru. Remove the string if you want to stop the recurring query execution!',
    ],
    'statsSummary' => [
        'upsell' => 'Also counts active customers that already had a valid tariff in the past (but e.g. had a tariff change within the given time span)',
    ],

    /*
     * MODULE: CRM
     */
    'crm' => [
        'contact' => [
            'type' => 'Select whether this is an individual person or an organization/company',
            'salutation' => 'Formal title or greeting (e.g., Mr., Mrs., Dr.)',
            'firstname' => 'First name of the individual contact',
            'lastname' => 'Last name of the individual contact',
            'company' => 'Company or organization name (required for organization type)',
            'email' => 'Primary email address for communication',
            'phone' => 'Primary phone number for communication',
            'birthday' => 'Date of birth (for individual contacts)',
            'apartment' => 'Optional link to a specific apartment in the property management system',
            'party_id_ext' => 'External identifier from TMF (TeleManagement Forum) or other external systems',
            'notes' => 'Additional information or comments about this contact',
        ],
        'lead' => [
            'source' => 'Select the source of this lead',
            'status' => 'Current status of the lead in the sales process',
            'legal_basis' => 'Legal basis for processing this lead\'s data',
            'apartment' => 'Optional link to a specific apartment in the property management system',
            'owner' => 'User responsible for managing this lead',
            'disqual_reason' => 'Reason why this lead was disqualified (if applicable)',
            'notes' => 'Additional information or comments about this lead',
        ],
        'opportunity' => [
            'status' => 'Current stage of the opportunity in the sales pipeline',
            'priority' => 'Business importance and urgency level',
            'source' => 'Origin of this business opportunity',
        ],
        'pipeline' => [
            'name' => 'Display name for the pipeline',
            'is_default' => 'Mark this pipeline as the default one for new opportunities',
            'stages' => 'Manage the stages that make up this pipeline workflow',
        ],
        'pipelineStage' => [
            'pipelineId' => 'Select the pipeline this stage belongs to',
            'name' => 'Display name for the stage',
            'orderIndex' => 'Position of this stage in the pipeline sequence (0-based)',
            'defaultProbabilityPct' => 'Default probability percentage for opportunities in this stage (0-100)',
            'color' => 'Color code for UI display (e.g., "#FF0000" or "red")',
            'isTerminal' => 'Mark if this is a final stage that stops the pipeline flow',
            'isWon' => 'Mark if this stage represents a won opportunity',
            'isLost' => 'Mark if this stage represents a lost opportunity',
        ],
        'stage_transition' => [
            'pipeline_id' => 'Select the pipeline this transition belongs to',
            'from_stage_id' => 'Select the starting stage for this transition',
            'to_stage_id' => 'Select the target stage for this transition',
            'guard_expr' => 'JSON expression that must evaluate to true for this transition to be allowed (optional)',
            'autofail_message' => 'Message to display when this transition fails validation (optional)',
        ],
        'opportunity' => [
            'contact_point_id' => 'Select the contact point for this opportunity',
            'created_from_lead_id' => 'Select the lead this opportunity was created from (optional, unique)',
            'realty_id' => 'Select the realty property for this opportunity (optional)',
            'apartment_id' => 'Select the specific apartment for this opportunity (optional)',
            'pipeline_id' => 'Select the sales pipeline for this opportunity',
            'stage_id' => 'Select the current stage in the pipeline',
            'amount_cents' => 'Enter the opportunity amount in cents (e.g., 10000 for €100.00)',
            'deal_size' => 'Enter the deal size in cents (e.g., 10000 for €100.00)',
            'probability_pct' => 'Enter the probability percentage (0-100)',
            'expected_close_date' => 'Select the expected closing date for this opportunity',
            'is_preorder' => 'Check if this is a pre-order opportunity',
            'is_switcher' => 'Check if this is a customer switching from another provider',
            'external_order_no' => 'Enter the external order number if applicable',
            'precheck_result' => 'Enter precheck results as JSON (optional)',
            'deal_terms_json' => 'Enter deal terms as JSON (optional)',
            'porting_requested_at' => 'Select when porting was requested (optional)',
            'porting_date' => 'Select the actual porting date (optional)',
            'contact_type_help' => 'Select whether this is an individual person or an organization/company',
        ],
    ],

    /*
     * MODULE: Contact Point (Global)
     */
    'contact_point' => [
        'type' => 'Select whether this is an individual person or an organization/company',
        'salutation' => 'Formal title or greeting (e.g., Mr., Mrs., Dr.)',
        'firstname' => 'First name of the individual contact',
        'lastname' => 'Last name of the individual contact',
        'company' => 'Company or organization name (required for organization type)',
        'email' => 'Primary email address for communication',
        'phone' => 'Primary phone number for communication',
        'birthday' => 'Date of birth (for individual contacts)',
        'apartment' => 'Optional link to a specific apartment in the property management system',
        'party_id_ext' => 'External identifier from TMF (TeleManagement Forum) or other external systems',
        'notes' => 'Additional information or comments about this contact',
    ],

    /*
     * MODULE: ContactBase (Global - same as contact_point for consistency)
     */
    'contact' => [
        'type' => 'Select whether this is an individual person or an organization/company',
        'salutation' => 'Formal title or greeting (e.g., Mr., Mrs., Dr.)',
        'firstname' => 'First name of the individual contact',
        'lastname' => 'Last name of the individual contact',
        'company' => 'Company or organization name (required for organization type)',
        'email' => 'Primary email address for communication',
        'phone' => 'Primary phone number for communication',
        'birthday' => 'Date of birth (for individual contacts)',
        'apartment' => 'Optional link to a specific apartment in the property management system',
        'party_id_ext' => 'External identifier from TMF (TeleManagement Forum) or other external systems',
        'notes' => 'Additional information or comments about this contact',
    ],

    /*
     * MODULE: Address (Global)
     */
    'address' => [
        'street' => 'Street name and number',
        'house_number' => 'House or building number',
        'zip' => 'Postal/ZIP code',
        'city' => 'City or town name',
        'district' => 'District or neighborhood',
        'source' => 'Source of the address data (e.g., manual entry, geocoding)',
        'lat' => 'Geographic latitude coordinate',
        'lng' => 'Geographic longitude coordinate',
    ],

    /*
     * CRM Opportunity Helper Texts
     */
    'amount_cents_help' => 'Enter the opportunity amount in cents (e.g., 10000 for €100.00)',
    'deal_size_help' => 'Enter the deal size in cents (e.g., 10000 for :currency100.00)',
    'probability_pct_help' => 'Enter the probability percentage (0-100)',
    'please_select_pipeline_first' => 'Please select a pipeline first to see available stages',

    /*
     * MODULE: CustomerInteraction (Global)
     */
    'ci_channel' => [
        'created' => 'Customer interaction channel has been created successfully.',
        'updated' => 'Customer interaction channel has been updated successfully.',
        'deleted' => 'Customer interaction channel has been deleted successfully.',
        'not_found' => 'Customer interaction channel not found.',
    ],
    'ci_direction' => [
        'created' => 'Customer interaction direction has been created successfully.',
        'updated' => 'Customer interaction direction has been updated successfully.',
        'deleted' => 'Customer interaction direction has been deleted successfully.',
        'not_found' => 'Customer interaction direction not found.',
    ],
    'ci_status' => [
        'created' => 'Customer interaction status has been created successfully.',
        'updated' => 'Customer interaction status has been updated successfully.',
        'deleted' => 'Customer interaction status has been deleted successfully.',
        'not_found' => 'Customer interaction status not found.',
    ],
    'ci_category' => [
        'created' => 'Customer interaction category has been created successfully.',
        'updated' => 'Customer interaction category has been updated successfully.',
        'deleted' => 'Customer interaction category has been deleted successfully.',
        'not_found' => 'Customer interaction category not found.',
    ],
    'ci_field' => [
        'created' => 'Customer interaction field has been created successfully.',
        'updated' => 'Customer interaction field has been updated successfully.',
        'deleted' => 'Customer interaction field has been deleted successfully.',
        'not_found' => 'Customer interaction field not found.',
    ],
    'ci_requirement_level' => [
        'created' => 'Customer interaction requirement level has been created successfully.',
        'updated' => 'Customer interaction requirement level has been updated successfully.',
        'deleted' => 'Customer interaction requirement level has been deleted successfully.',
        'not_found' => 'Customer interaction requirement level not found.',
    ],
    'ci_category_field_rule' => [
        'created' => 'Customer interaction category field rule has been created successfully.',
        'updated' => 'Customer interaction category field rule has been updated successfully.',
        'deleted' => 'Customer interaction category field rule has been deleted successfully.',
        'not_found' => 'Customer interaction category field rule not found.',
    ],
    'ci_customer_interaction' => [
        'created' => 'Customer interaction has been created successfully.',
        'updated' => 'Customer interaction has been updated successfully.',
        'deleted' => 'Customer interaction has been deleted successfully.',
        'not_found' => 'Customer interaction not found.',
    ],
];
