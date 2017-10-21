<?php
namespace AppBundle\Controller\API;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Lesson;
use AppBundle\Model\Proposition;


class StudyController extends Controller
{
    /**
     * @Route("/api/study/{lesson}/start",
     *        name="api_study_start",
     *        options={"expose"=true}
     *        )
     * @Method({"GET"})
     */
    public function startStudyAction(Lesson $lesson)
    {
        $sm = $this->get('app.study_manager');
        $exercise = $sm->startStudy($lesson);

        return new JsonResponse([
            'lessonTitle' => $lesson->getTitle(),
            'progress' => 0,
            'exerciseText' => $sm->getCurrentExerciseText()
        ]);
    }

    /**
     * @Route("/api/study/proposition/send",
     *        name="api_study_proposition_send",
     *        options={"expose"=true}
     *        )
     * @Method({"POST"})
     */
    public function sendPropositionAction(Request $request)
    {
        $sm = $this->get('app.study_manager');

        $proposition = new Proposition($request->get('text'));
        $correction = $sm->tryProposition($proposition);

        return new JsonResponse([
            'proposition' => $request->get('text'),
            'isOk' => $correction->isOk(),
            'remarks' => $correction->getRemarks(),
            'progress' => $sm->getProgress(),
        ]);
    }

    /**
     * @Route("/api/study/get_new_exercise",
     *        name="api_study_get_new_exercise",
     *        options={"expose"=true}
     *        )
     * @Method({"GET"})
     */
    public function getNewExeriseAction()
    {
        $sm = $this->get('app.study_manager');
        $exercise = $sm->getNextExercise();

        if (null !== $exercise) {
            return new JsonResponse([
                'progress' => $sm->getProgress(),
                'exerciseText' => $exercise->getText()
            ]);
        }

        $lm = $this->get('app.learning_manager');
        $em = $this->getDoctrine()->getManager();

        $successPercentage = $sm->getSuccessPercentage();

        $learning = $lm->finishLesson(
            $this->getUser(),
            $sm->getCurrentLesson(),
            $successPercentage
        );

        $mastery = $learning->getMastery();

        $em->flush();

        return new JsonResponse([
            'studyOver' => true,
            'successPercentage' => $successPercentage,
            'mastery' => $mastery,
        ]);
    }

    /** * @Route("/api/study/get_lesson_menu",
     *        name="api_study_get_lesson_menu",
     *        options={"expose"=true}
     *        )
     * @Method({"GET"})
     */
    public function getLessonMenuAction()
    {
        $lm = $this->get('app.lessonManager');
        $serializer = $this->get('jms_serializer');
        $lessons = $lm->getAllWithCurrentLearning($this->getUser());

        $response = new Response($serializer->serialize($lessons, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /** * @Route("/api/study/complaint",
     *        name="api_study_complaint",
     *        options={"expose"=true}
     *        )
     * @Method({"PUT"})
     */
    public function complaintAction()
    {
        $em = $this->getDoctrine()->getManager();
        $sm = $this->get('app.study_manager');
        $cm = $this->get('app.complaint_manager');
        $repoExercise = $em->getRepository("AppBundle:Exercise");

        $proposition = $sm->getLastSubmittedProposition();
        $exerciseText = $sm->getCurrentExerciseText();
        $exercise = $repoExercise->findOneBy(["text" => $exerciseText]);

        $complaint = $cm->addComplaint($exercise, $proposition);
        $em->flush();

        $message = "Bien reçu Michel !";

        if (null === $complaint) {
            $message = "C'est effectivement chelou ...";
        }

        return new JsonResponse(['message' => $message], 200);
    }
}
?>
