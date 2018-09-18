<?php
/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Component\Utils\ChamiloApi;

class IcpnaUpdateUserPlugin extends Plugin
{
    const SETTING_ENABLE = 'enable_hook';
    const SETTING_WEB_SERVICE = 'web_service';

    const DOCID_TYPE_DNI = '4b99eee6-3d67-45a3-b3ed-f15d6f9fbc9f';
    const DOCID_TYPE_CE = 'f86e0340-eb26-4b5d-8686-f24550fd8b49';
    const DOCID_TYPE_PP = '6f03e7df-13d1-4076-a3f0-b69fd1b3551a';

    const OCCUPATION_TYPE_SCH = '528c79fa-a924-4091-b25b-3f870650cf93';
    const OCCUPATION_TYPE_TEC = 'd4730b5f-e080-406d-846e-2f80c77f999c';
    const OCCUPATION_TYPE_UNI = '41ce5c66-1884-4a0d-8f7d-062a1ecb9b79';
    const OCCUPATION_TYPE_TRA = '30080101-15fa-46de-ac6e-a9d9a3c3d26e';

    /**
     * IcpnaUpdateUserPlugin constructor.
     */
    protected function __construct()
    {
        $options = [
            self::SETTING_ENABLE => 'boolean',
            self::SETTING_WEB_SERVICE => 'text'
        ];

        parent::__construct('1.1', 'Angel Fernando Quiroz Campos', $options);
    }

    /**
     * @return \IcpnaUpdateUserPlugin|null
     */
    static function create()
    {
        static $result = null;
        return $result ? $result : $result = new self();
    }

    /**
     * Get plugin name
     * @return string
     */
    public function get_name()
    {
        return 'icpna_update_user';
    }

    /**
     * @inheritDoc
     */
    public function performActionsAfterConfigure()
    {
        if ($this->get(self::SETTING_ENABLE) !== 'true') {
            $this->uninstallHook();

            return $this;
        }

        $this->installHook();

        return $this;
    }

    /**
     * Install hook for update user
     */
    private function installHook()
    {
        HookUpdateUser::create()->attach(
            IcpnaUpdateUserPluginHook::create()
        );
    }

    /**
     * Uninstall hook for update user
     */
    private function uninstallHook()
    {
        HookUpdateUser::create()->detach(
            IcpnaUpdateUserPluginHook::create()
        );
    }

    /**
     * @param string $functionName
     * @param array $params
     * @return array
     */
    private function getTableResult($functionName, $params = [])
    {
        $wsUrl = $this->get(self::SETTING_WEB_SERVICE);

        if (empty($wsUrl)) {
            return [];
        }

        $resultName = $functionName.'Result';

        try {
            $client = new SoapClient($wsUrl);
            $result = $client
                ->$functionName($params)
                ->$resultName
                ->any;

            $xml = strstr($result, '<diffgr:diffgram');

            $xmlResult = new SimpleXMLElement($xml);

            if (!isset($xmlResult->NewDataSet)) {
                return [];
            }

            return $xmlResult->NewDataSet->Table;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Call to tipodocumento function
     * @return array
     */
    public function getTipodocumento()
    {
        $return = [];
        $tableResult = $this->getTableResult('tipodocumento');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIdDocumentoIdentidad,
                'text' => (string) $item->vchNombreDocumento
            ];
        }

        return $return;
    }

    /**
     * Call to nacionalidad function
     * @return array
     */
    public function getNacionalidad()
    {
        $return = [];
        $tableResult = $this->getTableResult('nacionalidad');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uididpais,
                'text' => (string) $item->vchnombrepais
            ];
        }

        return $return;
    }

    /**
     * Call to departamento function
     * @return array
     */
    public function getDepartamento()
    {
        $return = [];
        $tableResult = $this->getTableResult('departamento');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIdDepartamento,
                'text' => (string) $item->vchNombreDepartamento
            ];
        }

        return $return;
    }

    /**
     * Call to provincia function
     * @param string $uididDepartamento
     * @return array
     */
    public function getProvincia($uididDepartamento)
    {
        $return = [];
        $tableResult = $this->getTableResult('provincia', ['uididdepartamento' => $uididDepartamento]);

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIdprovincia,
                'text' => (string) $item->vchNombreprovincia
            ];
        }

        return $return;
    }

    /**
     * Call to distrito function
     * @param string $uididProvincia
     * @return array
     */
    public function getDistrito($uididProvincia)
    {
        $return = [];
        $tableResult = $this->getTableResult('distrito', ['uididprovincia' => $uididProvincia]);

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIddistrito,
                'text' => (string) $item->vchNombredistrito
            ];
        }

        return $return;
    }

    /**
     * Call to ocupacion function
     * @return array
     */
    public function getOcupacion()
    {
        $return = [];
        $tableResult = $this->getTableResult('ocupacion');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uididocupacion,
                'text' => (string) $item->vchDescripcionOcupacion,
                'data-type' => (string) $item->chrTipoOcupacion
            ];
        }

        return $return;
    }

    /**
     * Call to centroestudios function
     * @param string $type
     * @param string $district
     * @return array
     */
    public function getCentroestudios($type, $district)
    {
        $return = [];
        $tableResult = $this->getTableResult(
            'centroestudios',
            ['chrTipoOcupacion' => $type, 'uididdistritocentroestudios' => $district]
        );

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIdCentroEstudios,
                'text' => (string) $item->vchDescripcionCentroEstudios
            ];
        }

        return $return;
    }

    /**
     * Call to centroestudios function
     * @return array
     */
    public function getCarrerauniversitaria()
    {
        $return = [];
        $tableResult = $this->getTableResult('carrerauniversitaria');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uididcarrerauniversitaria,
                'text' => (string) $item->vchcarrerauniversitaria
            ];
        }

        return $return;
    }

    /**
     * Convert SOAP result values to string
     * @param \SimpleXMLElement $objResult
     * @return array
     */
    private static function stringifyResult(SimpleXMLElement $objResult)
    {
        $toJson = json_encode($objResult);
        $toArray = json_decode($toJson, true);

        foreach ($toArray as &$item) {
            if (is_array($item) && empty($item)) {
                $item = '';
            }

            if (!is_array($item)) {
                $item = trim($item);
            }
        }

        return $toArray;
    }

    /**
     * Set the default fields to SOAP results
     * @param array $stringifiedResult
     * @return array
     */
    private static function filterResult(array $stringifiedResult)
    {
        return array_merge([
            'uidIdDocumentoIdentidad' => '',
            'vchDocumentoNumero' => '',
            'vchPrimerNombre' => '',
            'vchSegundoNombre' => '',
            'vchPaterno' => '',
            'vchMaterno' => '',
            'chrSexo' => '',
            'sdtFechaNacimiento' => '',
            'uididpaisorigen' => '',
            'uidIdDepartamento' => '',
            'uidIdProvincia' => '',
            'uidIdDistrito' => '',
            'vchNombreUrbanizacion' => '',
            'uidIdTipoVia' => '',
            'vchDireccionPersona' => '',
            'chrNroPuerta' => '',
            'chrNroInterior' => '',
            'vchEmailPersona' => '',
            'vchTelefonoPersona' => '',
            'vchcelularPersona' => '',
            'uidIdOcupacion' => '',
            'uididdepartamentocentroestudios' => '',
            'uididprovinciacentroestudios' => '',
            'uididdistritocentroestudios' => '',
            'uidIdCentroEstudios' => '',
            'vchcentrolaboral' => '',
            'uididcarrerauniversitaria' => '',
            'strNombrePadre' => '',
            'uidIdDocumentoIdentidadPadre' => '',
            'vchEmailApoderado' => '',
            'vchDocumentoNumeroPadre' => ''
        ], $stringifiedResult);
    }

    /**
     * Call to obtienedatospersonales function
     * @param $uididPersona
     * @return array
     */
    public function getUserInfo($uididPersona)
    {
        $tableResult = $this->getTableResult('obtienedatospersonales', ['uididpersona' => $uididPersona]);

        if (empty($tableResult)) {
            return [];
        }

        $tableResult = self::filterResult(
            self::stringifyResult($tableResult)
        );

        $birthdate = $tableResult['sdtFechaNacimiento'];
        $birthdate = explode('T', $birthdate);

        $return = [
            'extra_id_document_type' => $tableResult['uidIdDocumentoIdentidad'],
            'extra_id_document_number' => self::filterDocIdNumber(
                $tableResult['uidIdDocumentoIdentidad'],
                $tableResult['vchDocumentoNumero']
            ),
            'firstname' => self::filterPersonaName($tableResult['vchPrimerNombre']),
            'extra_middle_name' => self::filterPersonaName($tableResult['vchSegundoNombre']),
            'lastname' => self::filterPersonaName($tableResult['vchPaterno']),
            'extra_mothers_name' => self::filterPersonaName($tableResult['vchMaterno']),
            'extra_sex' => $tableResult['chrSexo'],
            'extra_birthdate' => $birthdate[0],
            'extra_nationality' => $tableResult['uididpaisorigen'],
            'extra_address_department' => $tableResult['uidIdDepartamento'],
            'extra_address_province' => $tableResult['uidIdProvincia'],
            'extra_address_district' => $tableResult['uidIdDistrito'],
            'extra_urbanization' => $tableResult['vchNombreUrbanizacion'],
            'extra_type_of_road' => $tableResult['uidIdTipoVia'],
            'extra_address' => $tableResult['vchDireccionPersona'],
            'extra_door_number' => $tableResult['chrNroPuerta'],
            'extra_indoor_number' => $tableResult['chrNroInterior'],
            'email' => self::filterEmail($tableResult['vchEmailPersona']),
            'phone' => $tableResult['vchTelefonoPersona'],
            'extra_mobile_phone_number' => $tableResult['vchcelularPersona'],
            'extra_occupation' => $tableResult['uidIdOcupacion'],
            'extra_occupation_department' => $tableResult['uididdepartamentocentroestudios'],
            'extra_occupation_province' => $tableResult['uididprovinciacentroestudios'],
            'extra_occupation_district' => $tableResult['uididdistritocentroestudios'],
            'extra_occupation_center_name_1' => self::OCCUPATION_TYPE_SCH === $tableResult['uidIdOcupacion']
                ? $tableResult['uidIdCentroEstudios']
                : '',
            'extra_occupation_center_name_2' => self::OCCUPATION_TYPE_TEC === $tableResult['uidIdOcupacion']
                ? $tableResult['uidIdCentroEstudios']
                : '',
            'extra_occupation_center_name_3' => self::OCCUPATION_TYPE_UNI === $tableResult['uidIdOcupacion']
                ? $tableResult['uidIdCentroEstudios']
                : '',
            'extra_occupation_center_name_4' => self::OCCUPATION_TYPE_TRA === $tableResult['uidIdOcupacion']
                ? $tableResult['vchcentrolaboral']
                : '',
            'extra_university_career' => $tableResult['uididcarrerauniversitaria'],
        ];

        $return['extra_guardian_name'] = self::filterPersonaName($tableResult['strNombrePadre']);
        $return['extra_guardian_id_document_type'] = $tableResult['uidIdDocumentoIdentidadPadre'];
        $return['extra_guardian_email'] = self::filterEmail($tableResult['vchEmailApoderado']);
        $return['extra_guardian_id_document_number'] = self::filterDocIdNumber(
            $return['extra_guardian_id_document_type'],
            $tableResult['vchDocumentoNumeroPadre']
        );

        if ($return['extra_id_document_number'] == $return['extra_guardian_id_document_number']) {
            $return['extra_guardian_id_document_number'] = '';
        }

        return $return;
    }

    /**
     * @param string $name
     * @return string
     */
    private static function filterPersonaName($name)
    {
        $name = trim($name);
        $name = filter_var(
            $name,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[a-zA-Zá-úÁ-ÚñÑ]+[a-zA-Zá-úÁ-ÚñÑ\s\-]*$/']]
        );

        return (string) $name;
    }

    /**
     * @param string $email
     * @return string
     */
    private static function filterEmail($email)
    {
        $email = trim($email);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        return (string) $email;
    }

    /**
     * @param string $type
     * @param string $number
     * @return string
     */
    private static function filterDocIdNumber($type, $number)
    {
        $number = trim($number);

        switch ($type) {
            case self::DOCID_TYPE_DNI:
                $number = filter_var($number, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^\d{8}$/']]);
                break;
            case self::DOCID_TYPE_CE:
                $number = filter_var(
                    $number,
                    FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => '/^[a-zA-Z0-9]{12}$/']]
                );
                break;
            case self::DOCID_TYPE_PP:
                $number = filter_var(
                    $number,
                    FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => '/^[a-zA-Z0-9]{9,12}$/']]
                );
                break;
            default:
                $number = '';
        }

        return (string) $number;
    }

    /**
     * Call to validaDocumentoIdentidad function
     * @param string $uididPersona
     * @param string $uididType
     * @param string $docNumber
     * @return bool
     */
    public function validateIdDocument($uididPersona, $uididType, $docNumber)
    {
        $wsUrl = $this->get(self::SETTING_WEB_SERVICE);

        if (empty($wsUrl)) {
            return true;
        }

        try {
            $client = new SoapClient($wsUrl);
            $result = $client
                ->validaDocumentoIdentidad([
                    'uididpersona' => $uididPersona,
                    'uididdocumentoidentidad' => $uididType,
                    'vchnumerodocumento' => $docNumber
                ])
                ->validaDocumentoIdentidadResult;

            return (bool) $result;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Call to validaDatosCompletos function
     * Web service returns 0 if the profile is completed, otherwiswe return 1
     * When webservice doesn't works then this function return true
     * @param string $uididpersona
     * @return bool Return true if profile is completed or when webservice fail
     */
    public function profileIsCompleted($uididpersona)
    {
        $wsUrl = $this->get(self::SETTING_WEB_SERVICE);

        if (empty($wsUrl)) {
            return true;
        }

        try {
            $client = new SoapClient($wsUrl);
            $result = $client
                ->validaDatosCompletos([
                    'uididpersona' => $uididpersona,
                ])
                ->validaDatosCompletosResult;

            return !$result;
        } catch (Exception $e) {
            //When webservice doesn't works then return true to avoid blocking access to users
            return true;
        }
    }

    /**
     * Redirect to redirect.php file to validate profile
     */
    public function redirect()
    {
        if ($this->get(self::SETTING_ENABLE) !== 'true') {
            return;
        }

        if (ChamiloApi::isAjaxRequest()) {
            return;
        }

        $userId = api_get_user_id();

        if (!$userId) {
            return;
        }

        if (!api_is_student()) {
            return;
        }

        $filter = [
            '/main/auth/profile.php',
            '/plugin/icpna_update_user/redirect.php'
        ];

        if (in_array($_SERVER['PHP_SELF'], $filter)) {
            return;
        }

        $efv = new ExtraFieldValue('user');
        $uididpersona = $efv->get_values_by_handler_and_field_variable($userId, 'uididpersona');

        $profileIsCompleted = $this->profileIsCompleted($uididpersona['value']);

        if ($profileIsCompleted) {
            return;
        }

        header('Location: '.api_get_path(WEB_CODE_PATH).'auth/profile.php');
        exit;
    }

    /**
     * Call to tipovia function
     * @return array
     */
    public function getTipoVia()
    {
        $return = [];
        $tableResult = $this->getTableResult('tipovia');

        foreach ($tableResult as $item) {
            $return[] = [
                'value' => (string) $item->uidIdTipoVia,
                'text' => (string) $item->vchNombreTipoVia,
            ];
        }

        return $return;
    }

    /**
     * @param string $strBirthdate Date of birthdate
     * @return bool
     */
    public static function isLegalAge($strBirthdate)
    {
        $birthdate = new DateTime($strBirthdate);
        $now = new DateTime();
        $interval = $now->diff($birthdate);
        $ageInDays = (int) $interval->format('%a');
        $adult = 18 * 365.25;
        $remainingDaysToBeAdult = $adult - $ageInDays;

        return $remainingDaysToBeAdult >= 0;
    }
}
