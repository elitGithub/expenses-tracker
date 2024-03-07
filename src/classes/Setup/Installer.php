<?php

declare(strict_types = 1);

namespace Setup;

use Core\System;
use Exception;

class Installer extends Setup
{

    protected System $system;
    /**
     * Array with user rights.
     *
     * @var array<array>
     */
    protected array $mainRights = [
        [
            'name'        => 'add_user',
            'description' => 'Right to add user accounts',
        ],
        [
            'name'        => 'edit_user',
            'description' => 'Right to edit user accounts',
        ],
        [
            'name'        => 'delete_user',
            'description' => 'Right to delete user accounts',
        ],
        //4 => "add_faq",
        [
            'name'        => 'add_faq',
            'description' => 'Right to add faq entries',
        ],
        //5 => "edit_faq",
        [
            'name'        => 'edit_faq',
            'description' => 'Right to edit faq entries',
        ],
        //6 => "delete_faq",
        [
            'name'        => 'delete_faq',
            'description' => 'Right to delete faq entries',
        ],
        //7 => "viewlog",
        [
            'name'        => 'viewlog',
            'description' => 'Right to view logfiles',
        ],
        //8 => "adminlog",
        [
            'name'        => 'adminlog',
            'description' => 'Right to view admin log',
        ],
        //9 => "delcomment",
        [
            'name'        => 'delcomment',
            'description' => 'Right to delete comments',
        ],
        //10 => "addnews",
        [
            'name'        => 'addnews',
            'description' => 'Right to add news',
        ],
        //11 => "editnews",
        [
            'name'        => 'editnews',
            'description' => 'Right to edit news',
        ],
        //12 => "delnews",
        [
            'name'        => 'delnews',
            'description' => 'Right to delete news',
        ],
        //13 => "addcateg",
        [
            'name'        => 'addcateg',
            'description' => 'Right to add categories',
        ],
        //14 => "editcateg",
        [
            'name'        => 'editcateg',
            'description' => 'Right to edit categories',
        ],
        //15 => "delcateg",
        [
            'name'        => 'delcateg',
            'description' => 'Right to delete categories',
        ],
        //16 => "passwd",
        [
            'name'        => 'passwd',
            'description' => 'Right to change passwords',
        ],
        //17 => "editconfig",
        [
            'name'        => 'editconfig',
            'description' => 'Right to edit configuration',
        ],
        //18 => "viewadminlink",
        [
            'name'        => 'viewadminlink',
            'description' => 'Right to see the link to the admin section',
        ],
        //19 => "backup delatt", // Duplicate, removed with 2.7.3
        //[
        //    'name' => 'delatt',
        //    'description' => 'Right to delete attachments'
        //],
        //20 => "backup",
        [
            'name'        => 'backup',
            'description' => 'Right to save backups',
        ],
        //21 => "restore",
        [
            'name'        => 'restore',
            'description' => 'Right to load backups',
        ],
        //22 => "delquestion",
        [
            'name'        => 'delquestion',
            'description' => 'Right to delete questions',
        ],
        //23 => 'addglossary',
        [
            'name'        => 'addglossary',
            'description' => 'Right to add glossary entries',
        ],
        //24 => 'editglossary',
        [
            'name'        => 'editglossary',
            'description' => 'Right to edit glossary entries',
        ],
        //25 => 'delglossary'
        [
            'name'        => 'delglossary',
            'description' => 'Right to delete glossary entries',
        ],
        //26 => 'changebtrevs'
        [
            'name'        => 'changebtrevs',
            'description' => 'Right to edit revisions',
        ],
        //27 => "addgroup",
        [
            'name'        => 'addgroup',
            'description' => 'Right to add group accounts',
        ],
        //28 => "editgroup",
        [
            'name'        => 'editgroup',
            'description' => 'Right to edit group accounts',
        ],
        //29 => "delgroup",
        [
            'name'        => 'delgroup',
            'description' => 'Right to delete group accounts',
        ],
        //30 => "addtranslation",
        [
            'name'        => 'addtranslation',
            'description' => 'Right to add translation',
        ],
        //31 => "edittranslation",
        [
            'name'        => 'edittranslation',
            'description' => 'Right to edit translations',
        ],
        //32 => "deltranslation",
        [
            'name'        => 'deltranslation',
            'description' => 'Right to delete translations',
        ],
        // 33 => 'approverec'
        [
            'name'        => 'approverec',
            'description' => 'Right to approve records',
        ],
        // 34 => 'addattachment'
        [
            'name'        => 'addattachment',
            'description' => 'Right to add attachments',
        ],
        // 35 => 'editattachment'
        [
            'name'        => 'editattachment',
            'description' => 'Right to edit attachments',
        ],
        // 36 => 'delattachment'
        [
            'name'        => 'delattachment',
            'description' => 'Right to delete attachments',
        ],
        // 37 => 'dlattachment'
        [
            'name'        => 'dlattachment',
            'description' => 'Right to download attachments',
        ],
        // 38 => 'reports'
        [
            'name'        => 'reports',
            'description' => 'Right to generate reports',
        ],
        // 39 => 'addfaq'
        [
            'name'        => 'addfaq',
            'description' => 'Right to add FAQs in frontend',
        ],
        // 40 => 'addquestion'
        [
            'name'        => 'addquestion',
            'description' => 'Right to add questions in frontend',
        ],
        // 41 => 'addcomment'
        [
            'name'        => 'addcomment',
            'description' => 'Right to add comments in frontend',
        ],
        // 42 => 'editinstances'
        [
            'name'        => 'editinstances',
            'description' => 'Right to edit multi-site instances',
        ],
        // 43 => 'addinstances'
        [
            'name'        => 'addinstances',
            'description' => 'Right to add multi-site instances',
        ],
        // 44 => 'delinstances'
        [
            'name'        => 'delinstances',
            'description' => 'Right to delete multi-site instances',
        ],
        [
            'name'        => 'export',
            'description' => 'Right to export the complete FAQ',
        ],
        [
            'name'        => 'view_faqs',
            'description' => 'Right to view FAQs',
        ],
        [
            'name'        => 'view_categories',
            'description' => 'Right to view categories',
        ],
        [
            'name'        => 'view_sections',
            'description' => 'Right to view sections',
        ],
        [
            'name'        => 'view_news',
            'description' => 'Right to view news',
        ],
        [
            'name'        => 'add_section',
            'description' => 'Right to add sections',
        ],
        [
            'name'        => 'edit_section',
            'description' => 'Right to edit sections',
        ],
        [
            'name'        => 'delete_section',
            'description' => 'Right to delete sections',
        ],
        [
            'name'        => 'administrate_sections',
            'description' => 'Right to administrate sections',
        ],
        [
            'name'        => 'administrate_groups',
            'description' => 'Right to administrate groups',
        ],
    ];

    /**
     * Configuration array.
     */
    protected array $mainConfig = [
        'main.currentVersion'              => null,
        'main.currentApiVersion'           => null,
        'main.language'                    => '__PHPMYFAQ_LANGUAGE__',
        'main.languageDetection'           => 'true',
        'main.phpMyFAQToken'               => null,
        'main.referenceURL'                => '__PHPMYFAQ_REFERENCE_URL__',
        'main.administrationMail'          => 'webmaster@example.org',
        'main.contactInformation'          => '',
        'main.enableAdminLog'              => 'true',
        'main.enableRewriteRules'          => 'false',
        'main.enableUserTracking'          => 'true',
        'main.metaDescription'             => 'phpMyFAQ should be the answer for all questions in life',
        'main.metaKeywords'                => '',
        'main.metaPublisher'               => '__PHPMYFAQ_PUBLISHER__',
        'main.send2friendText'             => '',
        'main.titleFAQ'                    => 'phpMyFAQ Codename Pontus',
        'main.urlValidateInterval'         => '86400',
        'main.enableWysiwygEditor'         => 'true',
        'main.enableWysiwygEditorFrontend' => 'false',
        'main.enableMarkdownEditor'        => 'false',
        'main.templateSet'                 => 'default',
        'main.optionalMailAddress'         => 'false',
        'main.dateFormat'                  => 'Y-m-d H:i',
        'main.maintenanceMode'             => 'false',
        'main.enableGravatarSupport'       => 'false',
        'main.enableGzipCompression'       => 'true',
        'main.customPdfHeader'             => '',
        'main.customPdfFooter'             => '',
        'main.enableSmartAnswering'        => 'true',
        'main.enableCategoryRestrictions'  => 'true',
        'main.enableSendToFriend'          => 'true',
        'main.privacyURL'                  => '',
        'main.enableAutoUpdateHint'        => 'true',
        'security.loginWithEmailAddress'   => 'false',
        'main.enableAskQuestions'          => 'false',
        'main.enableNotifications'         => 'false',
        'main.contactInformationHTML'      => 'false',

        'records.numberOfRecordsPerPage'     => '10',
        'records.numberOfShownNewsEntries'   => '3',
        'records.defaultActivation'          => 'false',
        'records.defaultAllowComments'       => 'false',
        'records.enableVisibilityQuestions'  => 'false',
        'records.numberOfRelatedArticles'    => '5',
        'records.orderby'                    => 'id',
        'records.sortby'                     => 'DESC',
        'records.orderingPopularFaqs'        => 'visits',
        'records.disableAttachments'         => 'true',
        'records.maxAttachmentSize'          => '100000',
        'records.attachmentsPath'            => 'attachments',
        'records.attachmentsStorageType'     => '0',
        'records.enableAttachmentEncryption' => 'false',
        'records.defaultAttachmentEncKey'    => '',
        'records.enableCloseQuestion'        => 'false',
        'records.enableDeleteQuestion'       => 'false',
        'records.randomSort'                 => 'false',
        'records.allowCommentsForGuests'     => 'true',
        'records.allowQuestionsForGuests'    => 'true',
        'records.allowNewFaqsForGuests'      => 'true',
        'records.hideEmptyCategories'        => 'false',
        'records.allowDownloadsForGuests'    => 'false',
        'records.numberMaxStoredRevisions'   => '10',
        'records.enableAutoRevisions'        => 'false',

        'search.numberSearchTerms'   => '10',
        'search.relevance'           => 'thema,content,keywords',
        'search.enableRelevance'     => 'false',
        'search.enableHighlighting'  => 'true',
        'search.searchForSolutionId' => 'true',
        'search.enableElasticsearch' => 'false',

        'security.permLevel'                       => 'basic',
        'security.ipCheck'                         => 'false',
        'security.enableLoginOnly'                 => 'false',
        'security.bannedIPs'                       => '',
        'security.ssoSupport'                      => 'false',
        'security.ssoLogoutRedirect'               => '',
        'security.useSslForLogins'                 => 'false',
        'security.useSslOnly'                      => 'false',
        'security.forcePasswordUpdate'             => 'false',
        'security.enableRegistration'              => 'true',
        'security.domainWhiteListForRegistrations' => '',
        'security.enableSignInWithMicrosoft'       => 'false',
        'security.enableGoogleReCaptchaV2'         => 'false',
        'security.googleReCaptchaV2SiteKey'        => '',
        'security.googleReCaptchaV2SecretKey'      => '',


        'spam.checkBannedWords'    => 'true',
        'spam.enableCaptchaCode'   => null,
        'spam.enableSafeEmail'     => 'true',
        'spam.manualActivation'    => 'true',
        'spam.mailAddressInExport' => 'true',

        'socialnetworks.enableTwitterSupport'     => 'false',
        'socialnetworks.twitterConsumerKey'       => '',
        'socialnetworks.twitterConsumerSecret'    => '',
        'socialnetworks.twitterAccessTokenKey'    => '',
        'socialnetworks.twitterAccessTokenSecret' => '',
        'socialnetworks.disableAll'               => 'false',

        'seo.metaTagsHome'       => 'index, follow',
        'seo.metaTagsFaqs'       => 'index, follow',
        'seo.metaTagsCategories' => 'index, follow',
        'seo.metaTagsPages'      => 'index, follow',
        'seo.metaTagsAdmin'      => 'noindex, nofollow',
        'seo.enableXMLSitemap'   => 'true',

        'mail.remoteSMTP'                           => 'false',
        'mail.remoteSMTPServer'                     => '',
        'mail.remoteSMTPUsername'                   => '',
        'mail.remoteSMTPPassword'                   => '',
        'mail.remoteSMTPPort'                       => '25',
        'mail.remoteSMTPEncryption'                 => '',
        'mail.remoteSMTPDisableTLSPeerVerification' => 'false',

        'ldap.ldapSupport'                            => 'false',
        'ldap.ldap_mapping.name'                      => 'cn',
        'ldap.ldap_mapping.username'                  => 'samAccountName',
        'ldap.ldap_mapping.mail'                      => 'mail',
        'ldap.ldap_mapping.memberOf'                  => '',
        'ldap.ldap_use_domain_prefix'                 => 'true',
        'ldap.ldap_options.LDAP_OPT_PROTOCOL_VERSION' => '3',
        'ldap.ldap_options.LDAP_OPT_REFERRALS'        => '0',
        'ldap.ldap_use_memberOf'                      => 'false',
        'ldap.ldap_use_sasl'                          => 'false',
        'ldap.ldap_use_multiple_servers'              => 'false',
        'ldap.ldap_use_anonymous_login'               => 'false',
        'ldap.ldap_use_dynamic_login'                 => 'false',
        'ldap.ldap_dynamic_login_attribute'           => 'uid',

        'api.enableAccess'   => 'true',
        'api.apiClientToken' => '',
    ];

    /**
     * Constructor.
     *
     * @throws \Exception
     */
    public function __construct(System $system)
    {
        parent::__construct();
        $this->system = $system;
        $dynMainConfig = [
            'main.currentVersion'    => System::getVersion(),
            'main.currentApiVersion' => System::getApiVersion(),
            'main.phpMyFAQToken'     => bin2hex(random_bytes(16)),
            'spam.enableCaptchaCode' => (extension_loaded('gd') ? 'true' : 'false'),
        ];
        $this->mainConfig = array_merge($this->mainConfig, $dynMainConfig);
    }
    /**
     * Check absolutely necessary stuff and die.
     * @throws Exception
     */
    public function checkBasicStuff(): void
    {
        if (!$this->checkMinimumPhpVersion()) {
            throw new Exception(
                sprintf('Sorry, but you need PHP %s or later!', System::VERSION_MINIMUM_PHP)
            );
        }

        if (!function_exists('date_default_timezone_set')) {
            throw new Exception(
                'Sorry, but setting a default timezone does not work in your environment!'
            );
        }

        if (!$this->system->checkDatabase()) {
            throw new Exception(
                'No supported database detected!'
            );
        }

        if (!$this->system->checkRequiredExtensions()) {
            throw new Exception(
                sprintf(
                    'Some required PHP extensions are missing: %s',
                    implode(', ', $this->system->getMissingExtensions())
                )
            );
        }

        if (!$this->system->checkInstallation()) {
            throw new Exception(
                'Expenses Tracker is already installed!'
            );
        }
    }
    public function checkFilesystemPermissions(): void
    {
        $instanceSetup = new Setup();
        $instanceSetup->setRootDir(EXTR_ROOT_DIR);

        $dirs = [
            '/config/config',
            '/config/data',
            '/config/logs',
            '/config/user',
            '/config/user/images',
            '/config/user/attachments',
        ];
        $failedDirs = $instanceSetup->checkDirs($dirs);
        $numDirs = count($failedDirs);

        if (1 <= $numDirs) {
            printf(
                '<p class="alert alert-danger">The following %s could not be created or %s not writable:</p><ul>',
                (1 < $numDirs) ? 'directories' : 'directory',
                (1 < $numDirs) ? 'are' : 'is'
            );
            foreach ($failedDirs as $failedDir) {
                echo "<li>{$failedDir}</li>\n";
            }

            printf(
                '</ul><p class="alert alert-danger">Please create %s manually and/or change access to chmod 775 (or ' .
                'greater if necessary).</p>',
                (1 < $numDirs) ? 'them' : 'it'
            );
        }
    }

    /**
     * Checks the minimum required PHP version, defined in System class.
     * Returns true if it's okay.
     */
    public function checkMinimumPhpVersion(): bool
    {
        return version_compare(PHP_VERSION, System::VERSION_MINIMUM_PHP) >= 0;
    }
}
