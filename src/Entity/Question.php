<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Expose;

use ApiPlatform\Core\Annotation as API;

use App\Model\Exercise;

/**
* @ORM\Entity
* @ORM\Table(name="question")
*
 * @API\ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"question.writeItem"}},
 *     attributes={"securit"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *          "get"={"security"="is_granted('ROLE_USER')"},
 *          "post"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"writeCollection"}}
 *          }
 *     },
 *     itemOperations={
 *          "get"={"security"="is_granted('ROLE_USER')"},
 *          "put"={
 *              "security"="is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"question.writeItem"}}
 *          },
 *          "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 *
 * @Serializer\ExclusionPolicy("all")
*/
class Question implements Exercise
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose()
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({"startLearningSession"})
     *
     * @Groups({"read", "writeLesson", "readItem", "writeCollection", "startLearningSession"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=225)
     *
     * @Serializer\Expose()
     * @Serializer\SerializedName("text")
     * @Serializer\Groups({"startLearningSession"})
     *
     * @Groups({"read", "question.writeItem", "writeLesson", "readItem", "startLearningSession"})
     */
    protected $text;

    /**
     * @ORM\OneToMany(targetEntity="Proposition", mappedBy="question", cascade={"persist", "remove"})
     * @ORM\OrderBy({"rightAnswer" = "DESC"})
     *
     * @Serializer\Expose()
     * @Serializer\SerializedName("propositions")
     * @Serializer\Groups({"startLearningSession"})
     *
     * @Groups({"readItem", "writeLesson", "startLearningSession"})
     */
    protected $propositions;

    /**
     * @ORM\ManyToOne(targetEntity="Proposition", inversedBy="rightAnswerFor", cascade={"persist"})
     * @ORM\JoinColumn(name="right_answer", referencedColumnName="id", nullable=true)
     */
    protected $answer;

    /**
     * @ORM\ManyToOne(targetEntity="Lesson", inversedBy="questions", cascade={"persist"})
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id")
     */
    protected $lesson;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\SerializedName("noPictures")
     *
     * @Groups({"read", "writeLesson", "readItem", "startLearningSession"})
     * @Serializer\Groups({"startLearningSession"})
     * @Serializer\Expose()
     */
    protected $noPictures;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\SerializedName("direction")
     *
     * @Groups({"read", "writeLesson", "readItem", "startLearningSession"})
     * @Serializer\Groups({"startLearningSession"})
     * @Serializer\Expose()
     */
    protected $direction;

    /**
     * @ORM\Column(type="integer", options={"default":1}, nullable=true)
     *
     * @Groups({"read", "writeLesson", "readItem", "startLearningSession"})
     * @Serializer\Expose()
     */
    protected $difficulty;

    public function __construct()
    {
        $this->propositions = new ArrayCollection();
        $this->noPictures = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

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

    public function getPropositions()
    {
        return $this->propositions;
    }

    public function setPropositions($propositions)
    {
        $this->propositions = $propositions;

        return $this;
    }

    public function addProposition($proposition)
    {
        $this->propositions[] = $proposition;
        $proposition->setQuestion($this);
    }

    public function removeProposition($proposition)
    {
        $this->propositions->removeElement($proposition);
    }

    /**
     * @Groups({"read", "readItem", "startLearningSession"})
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"startLearningSession"})
     * @Serializer\Expose()
     */
    public function getAnswer()
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('rightAnswer', true))
        ;

        $result = $this->propositions->matching($criteria);
        $result = $result->toArray();

        return array_pop($result);
    }

    public function setAnswer($answer)
    {
        if ($answer) {
            foreach ($this->propositions as $proposition) {
                $proposition->setRightAnswer(false);
            }

            $answer->setRightAnswer();
        }

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("type")
     */
    public function getExerciseType()
    {
        return 'question';
    }

    public function isNoPictures()
    {
        return $this->noPictures;
    }

    public function setNoPictures($noPictures)
    {
        $this->noPictures = $noPictures;

        return $this;
    }

    public function getDifficulty(): int
    {
        if (!$this->difficulty) {
            return 1;
        }

        return $this->difficulty;
    }

    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }
}
