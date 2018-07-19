<?php
/* For licensing terms, see /license.txt */

class IcpnaUpdateUserPluginHook extends HookObserver implements HookUpdateUserObserverInterface
{

    public static $plugin;

    /**
     * Constructor. Calls parent, mainly.
     */
    protected function __construct()
    {
        self::$plugin = IcpnaUpdateUserPlugin::create();
        parent::__construct(
            'plugin/icpna_update_user/IcpnaUpdateUserPluginHook.php',
            'icpna_update_user'
        );
    }

    /**
     * @param \HookUpdateUserEventInterface $hook
     * @return array
     */
    public function hookUpdateUser(HookUpdateUserEventInterface $hook)
    {
        $data = $hook->getEventData();

        if ($data['type'] !== HOOK_EVENT_TYPE_POST) {
            return $data;
        }

        $wsUrl = self::$plugin->get(IcpnaUpdateUserPlugin::SETTING_WEB_SERVICE);

        if (empty($wsUrl)) {
            return [];
        }

        $userInfo = api_get_user_info();
        $extraData = UserManager::get_extra_user_data($userInfo['id']);

        if (!isset($extraData['uididpersona'])) {
            return false;
        }

        $studyCenter = $extraData['occupation_center_name_1'];

        if ($extraData['occupation_center_name_2']) {
            $studyCenter = $extraData['occupation_center_name_2'];
        }

        if ($extraData['occupation_center_name_3']) {
            $studyCenter = $extraData['occupation_center_name_3'];
        }

        try {
            $client = new SoapClient($wsUrl);
            $result = $client
                ->actualizadatospersonales([
                    'p_uididpersona' => strtoupper($extraData['uididpersona']),
                    'p_uididdocumentoidentidad' => strtoupper($extraData['id_document_type']),
                    'p_vchDocumentoNumero' => $extraData['id_document_number'],
                    'p_chrSexo' => $extraData['sex'],
                    'p_sdtFechaNacimiento' => $extraData['birthdate'],
                    'p_uididpaisorigen' => strtoupper($extraData['nationality']),
                    'p_uidIdDepartamento' => strtoupper($extraData['address_department']),
                    'p_uidIdProvincia' => strtoupper($extraData['address_province']),
                    'p_uidIdDistrito' => strtoupper($extraData['address_district']),
                    'p_vchDireccionPersona' => $extraData['address'],
                    'p_vchEmailPersona' => $userInfo['email'],
                    'p_vchTelefonoPersona' => $userInfo['phone'],
                    'p_vchcelularPersona' => $extraData['mobile_phone_number'],
                    'p_uidIdOcupacion' => strtoupper($extraData['occupation']),
                    'p_uididdepartamentocentroestudios' => strtoupper($extraData['occupation_department']),
                    'p_uididprovinciacentroestudios' => strtoupper($extraData['occupation_province']),
                    'p_uididdistritocentroestudios' => strtoupper($extraData['occupation_district']),
                    'p_uidIdCentroEstudios' => strtoupper($studyCenter),
                    'p_vchcentrolaboral' => $extraData['occupation_center_name_4'],
                    'p_uididcarrerauniversitaria' => strtoupper($extraData['university_career']),
                    'p_strNombrePadre' => $extraData['guardian_name'],
                    'p_vchEmailApoderado' => $extraData['guardian_email'],
                    'p_uidIdDocumentoIdentidadPadre' => strtoupper($extraData['guardian_id_document_type']),
                    'p_vchDocumentoNumeroPadre' => $extraData['guardian_id_document_number'],
                    'p_vchNombreUrbanizacion' => $extraData['urbanization'],
                    'p_uidIdTipoVia' => $extraData['type_of_road'],
                    'p_chrNroPuerta' => $extraData['door_number'],
                    'p_chrNroInterior' => $extraData['indoor_number'],
                ])
                ->actualizadatospersonalesResult
                ->any;

            $xml = strstr($result, '<diffgr:diffgram');

            $xmlResult = new SimpleXMLElement($xml);

            if (!isset($xmlResult->NewDataSet)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Display::addFlash(
                Display::return_message($e->getMessage(), 'error')
            );

            return false;
        }

        return $data;
    }
}
