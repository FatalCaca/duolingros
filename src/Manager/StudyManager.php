<?php

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Model\PropositionInterface;
use App\Model\Proposition;
use App\Model\Exercise;
use App\Entity\Translation;
use App\Entity\Question;
use App\Manager\LessonManager;
use App\Manager\TranslationManager;
use App\Manager\QuestionManager;
use App\Manager\CorrectionManager;

class StudyManager
{
    const SERVICE_NAME = 'app.study_manager';

    protected $currentLesson;
    protected $currentLessonId;
    protected $currentExerciseType;
    protected $currentExerciseId;
    protected $targetAmountPlayed;
    protected $currentAmountPlayed;
    protected $currentAmountSucceeded;
    protected $user;

    protected $session;
    protected $lessonManager;
    protected $translationManager;
    protected $correctionManager;

    public function __construct(
        SessionInterface $session,
        LessonManager $lessonManager,
        TranslationManager $translationManager,
        QuestionManager $questionManager,
        CorrectionManager $correctionManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->session = $session;
        $this->lessonManager = $lessonManager;
        $this->translationManager = $translationManager;
        $this->questionManager = $questionManager;
        $this->correctionManager = $correctionManager;
        $this->user = $tokenStorage->getToken()->getUser();

        $session->start();
    }

    public function startStudy($lesson)
    {
        $this->setCurrentLessonId($lesson->getId());
        $this->setCurrentAmountPlayed(0);
        $this->setCurrentAmountSucceeded(0);
        $exercise = $this->getNextExercise();
        $this->setCurrentExerciseId($exercise->getId());
        $this->setCurrentExerciseType(get_class($exercise));

        return $exercise;
    }

    public function tryProposition(PropositionInterface $proposition)
    {
        $exercise = $this->getCurrentExercise();
        $correction = $this->correctionManager->correct($exercise, $proposition);
        $this->setLastSubmittedProposition($proposition);

        if ($correction->isOk()) {
            $this->setCurrentAmountSucceeded($this->getCurrentAmountSucceeded() + 1);
            $this->setLastSolvedExerciseId($exercise->getId());
        }

        $this->setCurrentAmountPlayed($this->getCurrentAmountPlayed() + 1);

        return $correction;
    }

    public function tryPropositionForExercise(
        PropositionInterface $proposition,
        Exercise $exercise
    ) {
        return $this->correctionManager->correct($exercise, $proposition);
    }

    public function getCurrentExercise(): Exercise
    {
        if ($this->getCurrentExerciseType() === Translation::Class) {
            return $this->translationManager->get($this->getCurrentExerciseId());
        }

        if ($this->getCurrentExerciseType() === Question::Class) {
            return $this->questionManager->get($this->getCurrentExerciseId());
        }
    }

    public function isOver()
    {
        return $this->getCurrentAmountSucceeded() >= $this->getTargetAmountPlayed();
    }

    public function getNextExercise()
    {
        if ($this->isOver()) {
            return null;
        }

        $lesson = $this->getCurrentLesson();
        $exercise = $lesson->getRandomExercise(
            $this->getLastSolvedExerciseId(),
            $this->getCurrentDifficulty()
        );
        $this->setCurrentExerciseId($exercise->getId());
        $this->setCurrentExerciseType(get_class($exercise));

        return $exercise;
    }

    public function getMistakes()
    {
        return $this->getCurrentAmountPlayed() / $this->getCurrentAmountSucceeded();
    }

    public function getSuccessPercentage()
    {
        return number_format(
            $this->getCurrentAmountSucceeded() / $this->getCurrentAmountPlayed() * 100,
            2
        );
    }

    public function getProgress()
    {
        return $this->getCurrentAmountSucceeded() / $this->getTargetAmountPlayed() * 100;
    }

    public function getCurrentLessonId()
    {
        return $this->session->get('current_lesson_id');
    }

    public function setCurrentLessonId($currentLessonId)
    {
        $this->session->set('current_lesson_id', $currentLessonId);

        return $this;
    }

    public function getCurrentExerciseId()
    {
        return $this->session->get('current_exercise_id');
    }

    public function setCurrentExerciseId($currentExerciseId)
    {
        $this->session->set('current_exercise_id', $currentExerciseId);

        return $this;
    }

    public function getCurrentExerciseType()
    {
        return $this->session->get('current_exercise_type');
    }

    public function setCurrentExerciseType($currentExerciseType)
    {
        $this->session->set('current_exercise_type', $currentExerciseType);

        return $this;
    }

    public function getTargetAmountPlayed()
    {
        return $this->session->get('target_amount_played');
    }

    public function setTargetAmountPlayed($targetAmountPlayed)
    {
        $this->session->set('target_amount_played', $targetAmountPlayed);

        return $this;
    }

    public function getCurrentAmountPlayed()
    {
        return $this->session->get('current_amount_played');
    }

    public function setCurrentAmountPlayed($currentAmountPlayed)
    {
        $this->session->set('current_amount_played', $currentAmountPlayed);

        return $this;
    }

    public function getCurrentAmountSucceeded()
    {
        return $this->session->get('current_amount_succeeded');
    }

    public function setCurrentAmountSucceeded($currentAmountSucceeded)
    {
        $this->session->set('current_amount_succeeded', $currentAmountSucceeded);

        return $this;
    }

    public function getCurrentLesson()
    {
        if ($this->currentLesson === null) {
            $this->currentLesson = $this->lessonManager->get($this->getCurrentLessonId());
        }

        return $this->currentLesson;
    }

    public function getCurrentLearning()
    {
        return $this->user->getLearningForLesson($this->getCurrentLesson());
    }

    public function getCurrentDifficulty()
    {
        $currentLearning = $this->getCurrentLearning();

        if (!$currentLearning) {
            return 0;
        }

        return $currentLearning->getDifficultyReached();
    }

    public function setLastSubmittedProposition($proposition)
    {
        $this->session->set('submitted_proposition', $proposition->getText());

        return $this;
    }

    public function getLastSubmittedProposition()
    {
        $sessionProposition = $this->session->get('submitted_proposition');

        if (!$sessionProposition) {
            return null;
        }

        $proposition = new Proposition($sessionProposition);

        return $proposition;
    }

    public function getLastSolvedExerciseId()
    {
        return $this->session->get('last_solved_exercise_id');
    }

    public function setLastSolvedExerciseId($id)
    {
        $this->session->set('last_solved_exercise_id', $id);

        return $this;
    }
}
