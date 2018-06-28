<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Chamilo\CourseBundle\Entity\CSurveyInvitation;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class CSurveyInvitationRepository
 * @package Chamilo\CourseBundle\Entity\Repository
 */
class CSurveyInvitationRepository extends EntityRepository
{
    /**
     * Find a survey invitation to be shown in modal dialog box.
     * Requires than allow_survey_in_modal is enabled in configuration file.
     *
     * @param int $userId    User ID.
     * @param int $courseId  Course ID.
     * @param int $sessionId Optional. Session ID.
     *
     * @return CSurveyInvitation|null
     */
    public function findOneToModal($userId, $courseId, $sessionId = 0)
    {
        $allowSurveyAvailabilityDatetime = api_get_configuration_value('allow_survey_availability_datetime');
        $now = new \DateTime('UTC', new \DateTimeZone('UTC'));

        try {
            /** @var CSurveyInvitation $invitation */
            $invitation = \Database::getManager()
                ->createQuery("
                        SELECT i FROM ChamiloCourseBundle:CSurveyInvitation i
                        INNER JOIN ChamiloCourseBundle:CSurvey s
                            WITH (s.code = i.surveyCode AND s.cId = i.cId AND s.sessionId = i.sessionId)
                        WHERE i.answered = 0
                            AND i.cId = :course
                            AND i.user = :user
                            AND i.sessionId = :session
                            AND :now BETWEEN s.availFrom AND s.availTill
                        ORDER BY s.availTill ASC
                    ")
                ->setMaxResults(1)
                ->setParameters([
                    'course' => $courseId,
                    'user' => $userId,
                    'session' => $sessionId,
                    'now' => $allowSurveyAvailabilityDatetime ? $now->format('Y-m-d H:i:s') : $now->format('Y-m-d')
                ])
                ->getSingleResult();
        } catch (NoResultException $e) {
            $invitation = null;
        } catch (NonUniqueResultException $e) {
            $invitation = null;
        } catch (\Exception $e) {
            $invitation = null;
        }

        return $invitation;
    }
}
