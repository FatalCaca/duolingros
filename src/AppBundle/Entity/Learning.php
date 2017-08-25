<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="learning")
 */
class Learning
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="learnings", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Lesson", inversedBy="learnings", cascade={"persist"})
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id")
     */
    protected $lesson;

    /**
     * @ORM\Column(type="integer")
     */
    protected $goodStreak;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastPractice;

    /**
     * @ORM\Column(type="integer")
     */
    protected $vacationDays;

    public function __construct()
    {
        $this->goodStreak = 0;
        $this->vacationDays = -1;
        $this->lastPractice = new \DateTime();
        $this->lastPractice->modify('-1 day');
    }

    public function getMastery()
    {
        if ($this->getGoodStreak() > 0) {
            return 100;
        }

        $diff = $this->lastPractice->diff($this->getNextPractice());
        $dayDiff = $diff->format('%R');
        $dayDiff -= $this->vacationDays;

        $mastery = 100 - 10 * $dayDiff;

        if ($mastery <= 0) {
            $mastery = 0;
        }

        if ($mastery >= 100) {
            $mastery = 100;
        }

        return $mastery;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getLesson()
    {
        return $this->lesson;
    }

    public function setLesson($lesson)
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getGoodStreak()
    {
        return $this->goodStreak;
    }

    public function setGoodStreak($goodStreak)
    {
        $this->goodStreak = $goodStreak;

        return $this;
    }

    public function getLastPractice()
    {
        return $this->lastPractice;
    }

    public function setLastPractice($lastPractice)
    {
        $this->lastPractice = $lastPractice;

        return $this;
    }

    public function getNextPractice()
    {
        $nextPractice = clone $this->lastPractice;
        $nextPractice->modify('+1 day');
        $nextPractice->modify('+' . $this->vacationDays . 'day');

        return $nextPractice;
    }

    public function setNextPractice($nextPractice)
    {
        $this->nextPractice = $nextPractice;

        return $this;
    }

    public function getVacationDays()
    {
        return $this->vacationDays;
    }

    public function setVacationDays($vacationDays)
    {
        $this->vacationDays = $vacationDays;

        if ($this->vacationDays <= -1) {
            $this->vacationDays = -1;
        }

        return $this;
    }

    public function increaseGoodStreak()
    {
        $this->goodStreak += 1;
        return $this;
    }

    public function resetGoodStreak()
    {
        $this->goodStreak = 0;
        return $this;
    }

    public function increaseVacationDays()
    {
        $this->vacationDays += 1;
        return $this;
    }

    public function decreaseVacationDays()
    {
        $this->vacationDays -= 1;
        return $this;
    }
}