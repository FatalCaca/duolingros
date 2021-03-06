<?php
namespace App\Model;

use App\Model\Exercise;
use App\Tool\StringComparer;
use Psr\Log\LoggerInterface;

class RegexCorrector implements CorrectorInterface
{
    const THRESHOLD_FOR_GUESSING = 15;
    const THRESHOLD_FOR_GUESSING_WORD = 2;
    const THRESHOLD_FOR_ACCEPTING = 1;

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function correct(
        Exercise $translation,
        PropositionInterface $proposition
    ) {
        $answers = $translation->getConcreteAnswers();
        $this->logger->debug("Début de correction de $proposition");

        $correction = new Correction();

        $purifiedProposition = $this->purifyString($proposition->getText());
        $this->logger->debug("Proposition analysée : $purifiedProposition");

        foreach ($answers as $answer) {
            $purifiedAnswer = $this->purifyString($answer);
            $this->logger->debug("Réponse envisagée : $purifiedAnswer");

            if(preg_match(sprintf("/^%s$/i", $purifiedAnswer), $purifiedProposition)) {
                $this->logger->debug("Match parfait trouvé");
                return $correction;
            }
        }

        $this->logger->debug("Aucun match parfait trouvé");

        $closestGoodAnswer = StringComparer::findClosestCandidate(
            $proposition->getText(),
            $answers
        );

        $correction->setRightAnswer($closestGoodAnswer);

        $purifiedAnswer = $this->purifyString($closestGoodAnswer);
        $this->logger->debug("Réponse la plus proche de la proposition : $purifiedAnswer");

        $distance = levenshtein($purifiedAnswer, $purifiedProposition);
        $this->logger->debug("Distance avec la proposition : $distance");

        if ($distance <= self::THRESHOLD_FOR_ACCEPTING) {
            $this->logger->debug("Réponse acceptée malgré la distance");
            $correction->setIsOkDespiteRemark(true);
        }

        if ($distance != 0) {
            if ($distance <= self::THRESHOLD_FOR_GUESSING) {
                $correctedAnswer = $this->generateCorrectedAnswer(
                    $proposition->getText(),
                    $closestGoodAnswer
                );

                $correction->addRemark("Vouliez-vous dire \"$correctedAnswer\" ?");
            }
            else {
                $correctedAnswer = "<strong>" . $closestGoodAnswer . "<strong>";
                $correction->addRemark("On aurait pu dire : \"$correctedAnswer\" ?");
            }
        }

        return $correction;
    }

    public function generateCorrectedAnswer($actual, $expected)
    {
        $this->logger->debug("Génération d'une proposition corrigée (\"$actual\" vs \"$expected\")");
        $correctedAnswer = "";
        $actualWords = explode(' ', $actual);
        $expectedWords = explode(' ', $expected);

        $this->logger->debug(sprintf("Mots attendus : ", implode('|', $expectedWords)));
        $this->logger->debug(sprintf("Mots obtenus : ", implode('|', $actualWords)));

        $actualWordSelectionOffset = 0;

        foreach ($expectedWords as $index => $expectedWord) {
            $actualWord = "";

            if (isset($actualWords[$index + $actualWordSelectionOffset])) {
                $actualWord = $actualWords[$index + $actualWordSelectionOffset];
            }

            $nextActualWord = "";

            if (isset($actualWords[$index + $actualWordSelectionOffset + 1])) {
                $nextActualWord = $actualWords[$index + $actualWordSelectionOffset + 1];
            }

            $nextExpectedWord = "";

            if (isset($expectedWords[$index + 1])) {
                $nextExpectedWord = $expectedWords[$index + 1];
            }

            $this->logger->debug("Comparaison de $expectedWord vs $actualWord");

            if ($expectedWord != $actualWord) {
                $this->logger->debug("Échec de la comparaison");

                if ($actualWord == $nextExpectedWord) {
                    $correctedAnswer .= "<strong>$expectedWord</strong> ";
                    $this->logger->debug("Il y avait un oubli mot ($expectedWord). On le met en gras");

                    $actualWordSelectionOffset --;

                    continue;
                }

                if ($nextActualWord == $expectedWord) {
                    $correctedAnswer .= "<strike>$actualWord</strike> ";
                    $this->logger->debug("Il y avait un mot de trop ($actualWord). On raye le mot actuel");

                    $actualWordSelectionOffset ++;

                    continue;
                }

                $correctedAnswer .= "<strike>$actualWord</strike> <strong>$expectedWord</strong>";
                $this->logger->debug("Erreur simple. Rayage et remplacement");
            }
            else {
                $correctedAnswer .= $actualWord;
            }

            $correctedAnswer .= " ";
        }

        $correctedAnswer = trim($correctedAnswer);
        $this->logger->debug("Réponse corrigée : " . $correctedAnswer);

        return $correctedAnswer;
    }

    public function purifyString($string)
    {
        $purifiedString = preg_replace('/[.,:\-;?\']/', ' ', $string);
        $purifiedString = strtolower($purifiedString);
        $purifiedString = preg_replace('/  /', ' ', $purifiedString);
        $purifiedString = preg_replace('/  /', ' ', $purifiedString);
        $purifiedString = preg_replace('/  /', ' ', $purifiedString);
        $purifiedString = preg_replace('/  /', ' ', $purifiedString);
        $purifiedString = preg_replace('/^/', '', $purifiedString);
        $purifiedString = preg_replace('/[éèê]/', 'e', $purifiedString);
        $purifiedString = preg_replace('/[àâ]/', 'a', $purifiedString);
        $purifiedString = preg_replace('/[îï]/', 'i', $purifiedString);
        $purifiedString = preg_replace('/[îï]/', 'i', $purifiedString);
        $purifiedString = trim($purifiedString);

        return $purifiedString;
    }
}

