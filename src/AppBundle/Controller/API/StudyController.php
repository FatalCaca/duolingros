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
        $exercise = $sm->getNextExercise();

        return new JsonResponse([
            'isOk' => $correction->isOk(),
            'remarks' => $correction->getRemarks(),
            'progress' => $sm->getProgress(),
            'exerciseText' => $exercise->getText()
        ]);
    }
}

?>
