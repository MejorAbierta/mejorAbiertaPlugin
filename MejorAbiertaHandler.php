<?php

namespace APP\plugins\generic\mejorAbierta;

use APP\handler\Handler;

use PKP\handler\APIHandler;
use PKP\core\APIResponse;
use Slim\Http\Request as SlimRequest;

use APP\facades\Repo;
use PKP\security\Role;
use PKP\db\DAORegistry;
use PKP\submission\PKPSubmission;

use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\ContextRequiredPolicy;

use PKP\security\authorization\UserRolesRequiredPolicy;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;

use APP\security\authorization\OjsIssueRequiredPolicy;
use APP\security\authorization\OjsJournalMustPublishPolicy;

use PKP\security\authorization\PKPSiteAccessPolicy;


use APP\core\Application;


class MejorAbiertaHandler extends APIHandler
{
    var $bannedFields = [
        'password',
        'apiKey',
        'email',
        // 'familyName',
        // 'givenName',
        'accessStatus'
    ];


    public function __construct()
    {

        //PKPSiteAccessPolicy::SITE_ACCESS_ALL_ROLES
        $this->_handlerPath = 'mejorAbierta';

        //COOKIE auth
        $roles = [Role::ROLE_ID_AUTHOR];
        $this->_endpoints = [
            'GET' => [
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'about'],
                    //'roles' => $roles,
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'reviewers'],
                    //'roles' => $roles,
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'journalIdentity'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'announcement'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'author'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'category'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'citation'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'decision'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'institution'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'submissionFile'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'userGroup'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'representation'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'DAOs'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'DAO'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'submissions'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'issues'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'urls'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'reviews'],
                ],
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'eventlogs'],
                ],

                /*  [
                    'pattern' => $this->getEndpointPattern() . '/current',
                    'handler' => [$this, 'getCurrent'],
                    'roles' => $roles
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '/{issueId:\d+}',
                    'handler' => [$this, 'get'],
                    'roles' => $roles
                ],*/
            ]
        ];
    }
    public function authorize($request, &$args, $roleAssignments)
    {
        $headers = getallheaders();
        $token = $headers['Authorization'];
        $this->read_env_file();
        $api_token = getenv('API_TOKEN');

        if ($token != "Bearer $api_token") {
            return $request->getDispatcher()->handle404();
        }
        return parent::authorize($request, $args, $roleAssignments);
    }

    function read_env_file()
    {
        $file_path = dirname(__FILE__, 1) . '/.env';
        if (!file_exists($file_path)) {
            return;
        }

        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
            }
        }
    }


    /*

	function authorize($request, &$args, $roleAssignments) {
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}
*//*
    public function authorize($request, &$args, $roleAssignments)
    {
        $apiKey = $_SERVER['Authorization'];
        echo $apiKey;
        if ($apiKey) {
            // Para API Key, solo verificamos que la key sea válida
            $this->addPolicy(new ApiKeyAuthorizationPolicy($request, null, $apiKey));
        } else {
            // Para acceso normal, verificamos roles de usuario
            $this->addPolicy(new UserRolesRequiredPolicy($request), true);

            $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);
            foreach ($roleAssignments as $role => $operations) {
                $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
            }
            $this->addPolicy($rolePolicy);
        }
        return parent::authorize($request, $args, $roleAssignments);
    }*/

    public function announcement($args, $request)
    {

        $data = Repo::announcement()
            ->getCollector()
            ->getMany();


        echo json_encode($data);
    }

    public function author($args, $request)
    {

        $data = Repo::author()
            ->getCollector()
            ->getMany();


        echo json_encode($this->anonimizeData($data));
    }

    public function category($args, $request)
    {

        $data = Repo::category()
            ->getCollector()
            ->getMany();


        echo json_encode($data);
    }

    public function decision($args, $request)
    {

        $data = Repo::decision()
            ->getCollector()
            ->getMany();


        echo json_encode($data);
    }

    public function institution($args, $request)
    {

        $data = Repo::institution()
            ->getCollector()
            ->getMany();


        echo json_encode($data);
    }


    function submissionFile($args, $request)
    {

        $data = Repo::submissionFile()
            ->getCollector();

        if (isset($args[0])) {
            $data->filterBySubmissionIds([$args[0]]);
        }

        $data = $data->getMany();
        //echo json_encode($data);

        $files = [];
        foreach ($data as $key => $value) {
            $path = \Config::getVar('files', 'files_dir') . '/' . $value->getData('path');
            $fileName = $value->getLocalizedData('name');
            if (file_exists($path)) {

                $files[] = [
                    'path' => $path,
                    'name' => $fileName,
                ];
            }
        }

        if (count($files) > 0) {
            $this->compressAndDownload($files, "submissionFiles.zip");
        }
    }

    function compressAndDownload($files, $zipFileName)
    {
        // Create a new zip file
        $zip = new \ZipArchive();
        $tempZipFile = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($tempZipFile, \ZipArchive::CREATE) === TRUE) {
            // Add files to the zip
            foreach ($files as $file) {
                $zip->addFile($file['path'], $file['name']);
            }
            // Close the zip file
            $zip->close();

            // Output the zip file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($tempZipFile));
            ob_clean();
            flush();
            readfile($tempZipFile);
            unlink($tempZipFile);
            exit;
        } else {
            echo "Error creating zip file.";
        }
    }

    function downloadFile($filePath, $fileName)
    {
        // Check if the file exists
        if (!file_exists($filePath)) {
            echo "File not found.";
            return;
        }

        // Get the file size
        $fileSize = filesize($filePath);

        // Get the file type
        $fileType = mime_content_type($filePath);

        // Set the headers
        header('Content-Type: ' . $fileType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);

        // Read the file and output it
        readfile($filePath);
    }


    public function userGroup($args, $request)
    {

        $data = Repo::userGroup()
            ->getCollector()
            ->getMany();


        echo json_encode($data);
    }

    function representation()
    {
        $contextId = Application::CONTEXT_JOURNAL;
        /** @var ContextDAO $contextDao */
        $representationDAO = Application::getRepresentationDAO();
        /** @var Context $context */
        $representation = $representationDAO->getById($contextId);

        echo json_encode($this->anonimizeData($representation));
    }


    public function reviewers($args, $request)
    {

        $data = Repo::user()
            ->getCollector()
            ->filterByRoleIds([Role::ROLE_ID_REVIEWER])
            ->getMany();



        echo json_encode($this->anonimizeData($data));
    }





    function journalIdentity()
    {
        $contextId = Application::CONTEXT_JOURNAL;
        /** @var ContextDAO $contextDao */
        $contextDao = Application::getContextDAO();
        /** @var Context $context */
        $context = $contextDao->getById($contextId);

        /*
        $text = "Datos de la revista" . "|";
        $text .= "Nombre: " . implode(",", $context->getSetting('name')) . "|";
        $text .= "ISSN: " . $context->getSetting('printIssn') . "|";
        $text .= "ISSN electrónico: " . $context->getSetting('onlineIssn') . "|";
        $text .= "Entidad: " . $context->getSetting('publisherInstitution');
        */
        $context->printIssn = $context->getSetting('printIssn');
        $context->onlineIssn = $context->getSetting('onlineIssn');
        $context->publisherInstitution = $context->getSetting('publisherInstitution');

        echo json_encode($this->anonimizeData($context));
    }

    function DAOs()
    {
        $data = DAORegistry::getDAOs();

        echo json_encode($data);
    }

    function DAO($args)
    {
        $data = DAORegistry::getDAO($args[0]);
        echo json_encode($data);
    }

    function about(): string
    {
        $contextId = Application::CONTEXT_JOURNAL;
        /** @var ContextDAO $contextDao */
        $contextDao = Application::getContextDAO();
        /** @var Context $context */
        $context = $contextDao->getById($contextId);
        return implode(",", $context->getData('about'));
    }


    function submissions()
    {
        /*
        public const STATUS_QUEUED = 1;
        public const STATUS_PUBLISHED = 3;
        public const STATUS_DECLINED = 4;
        public const STATUS_SCHEDULED = 5;
        */

        $contextId = Application::CONTEXT_JOURNAL;
        $submissions = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$contextId])
            //->filterByStatus($status)
            ->getMany();

        /*
        $filteredElements = $submissions->filter(function ($element) {
            return $element->getData("dateSubmitted") >= date("", 1741873765);
        });
        */

        $json = json_encode($submissions);
        //2025-02-13 00:00:00

        //return "" . $filteredElements;

        echo json_encode($this->anonimizeData($submissions));
    }

    function issues()
    {

        $contextId = Application::CONTEXT_JOURNAL;
        $data = Repo::issue()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->filterByPublished(true)
            //->filterByStatus($status)
            ->getMany();

        $result = [];
        foreach ($data as $key => $item) {
            //echo json_encode($item->_data["id"]);
            $item->_data["submissions"] = Repo::submission()
                ->getCollector()
                ->filterByContextIds([$contextId])
                ->filterByIssueIds([$item->_data["id"]])
                ->filterByStatus([PKPSubmission::STATUS_PUBLISHED])
                ->orderBy('seq', 'ASC')
                ->getMany();

            $result[] = $item;
        }

        echo json_encode($result);
        //echo json_encode($this->anonimizeData($data));
    }

    function section()
    {
        $contextId = Application::CONTEXT_JOURNAL;
        $data = Repo::section()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->getMany();

        echo json_encode($this->anonimizeData($data));
    }

    function urls($args, $request)
    {
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();
        $data = [];
        $data["home"] = $dispatcher->url($request, ROUTE_PAGE, null, 'index', null, null);
        $data["editorialTeam"] = $dispatcher->url($request, ROUTE_PAGE, null, 'about', 'editorialTeam');
        $data["submissions"] = $dispatcher->url($request, ROUTE_PAGE, null, 'about', 'submissions');
        $data["about"] = $dispatcher->url($request, ROUTE_PAGE, null, 'about');
        $data["privacy"] = $dispatcher->url($request, ROUTE_PAGE, null, 'about', 'privacy');
        $data["contact"] = $dispatcher->url($request, ROUTE_PAGE, null, 'about', 'contact');

        echo json_encode($data);
    }

    function reviews($args)
    {
        //this seems to return empty...?
        $submissionId = $args[0];

        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignments = $reviewAssignmentDao->getBySubmissionId($submissionId);

        echo json_encode($reviewAssignments);
    }

    function eventlogs($args)
    {
        $submissionId = $args[0];

        $eventLogs = Repo::eventLog()
            ->getCollector()
            ->filterByAssoc(Application::ASSOC_TYPE_SUBMISSION, [$submissionId])
            ->getMany();
        echo json_encode($eventLogs);
    }


    function anonimizeData($data)
    {

        if (get_class($data) != 'Illuminate\Support\LazyCollection') {
            $data = collect([$data]);
        }

        return $data->map(function ($item) {

            $item = (array) $item;
            foreach ($this->bannedFields as $key => $value) {
                if (!isset($item['_data'])) {
                    break;
                }
                unset($item['_data'][$value]);

                if (isset($item['_data']['publications'])) {
                    $item['_data']['publications'] = $this->anonimizeData($item['_data']['publications']);
                }

                if (isset($item['_data']['authors'])) {
                    $item['_data']['authors'] = $this->anonimizeData($item['_data']['authors']);
                }
            }

            return (object) $item;
        });
    }
}
