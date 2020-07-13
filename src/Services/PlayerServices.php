<?php


namespace App\Services;


use App\Controller\PlayerController;
use App\Entity\Enigma;
use App\Entity\Player;
use App\Entity\PlayerEnigma;
use App\Repository\PlayerEnigmaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PlayerServices
{

    public function __construct()
    {
    }

    public function recupererLaListeDesEnigmes(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, EntityManagerInterface $entityManager)
    {
        $listPlayerEnigma = $playerEnigmaRepository->findPlayerEnigmaAndEnigmaByPlayer($player);

        if ($player->getTeam()->getDeadLine() == null && $player->getDeadLine() == null) {
            $now = new \DateTime();
            //Décalage Horaire de +2h par rapport au 00:00
            $now->add(new \DateInterval('PT2H'));

            $timestmpTempsJeu = $player->getTeam()->getTimeTeam()->getTimestamp();
            $timestmpNow = $now->getTimestamp();

            $timestpdeadline = $timestmpNow + $timestmpTempsJeu;
            $deadLine = new \DateTime();
            $deadLine->setTimestamp($timestpdeadline);
            $player->setDeadLine($deadLine);


            $entityManager->persist($player);
            $entityManager->flush();
        }

        return $listPlayerEnigma;
    }

    public function checkBeforShowEnigma(Player $player, PlayerEnigmaRepository $playerEnigmaRepository, Enigma $enigma, EntityManagerInterface $em)
    {
        // On recupere le tableau de playerEnigma qui lui est lier
        $listPlayerEnigma = $playerEnigmaRepository->findBy(['player' => $player]);
        // On initialise a boolean a false partant du principe qu'il n'as pas cette enigme
        $bool = false;
        // On parcour la liste des playerEnigma
        foreach ($listPlayerEnigma as $playerEnigma) {
            // Si a un moment une des enigmes equivaut l'enigme a la quelle il veut acceder
            if ($playerEnigma->getEnigma() === $enigma) {
                // On passe le boolean a true
                $bool = true;
                // On passe l'enigme en ouverte si elle ne l'etait pas
                // Si elle est deja resolue on genere une exception
                switch ($playerEnigma->getSolved()) {

                    case 0 :
                        $playerEnigma->setSolved(1);
                        $em->persist($playerEnigma);
                        $em->flush();
                        break;

                    case 3 :
                        return new AccessDeniedException("Vous avez deja resolu cette enigme !");
                        break;
                }
                // On stop la boucle
                break;
            }
        }
        // Si le boolean est toujours false, on genere une exeception
        if (!$bool) return new AccessDeniedException("Cette enigme ne fait pas partie de votre session");
        return null;
    }

    public function checkAnswer(Player $player, Enigma $enigma, string $answer, EntityManagerInterface $em, PlayerEnigmaRepository $playerEnigmaRepository)
    {
        // Incremente le nombre de tentative
        $playerEnigma = $playerEnigmaRepository->findOneBy(['player' => $player, 'enigma' => $enigma]);

        if ($playerEnigma instanceof PlayerEnigma) {
            $playerEnigma->setTry($playerEnigma->getTry() + 1);
        }

        $goodChara = 0;

        try {
            for ($i = 0; $i < strlen($answer); $i++) {
                if ($enigma->getAnswer()[$i] == $answer[$i]) $goodChara += 1;
            }
        } catch (\ErrorException $e) {
            return 1;
        }

        $average = ($goodChara / strlen($enigma->getAnswer())) * 100;

        switch ($average) {

            case $average == 100 :
                $playerEnigma->setSolved(3);
                $em->persist($playerEnigma);
                $em->flush();
                return 3;

            case $average >= 50 :
                $playerEnigma->setSolved(2);
                $em->persist($playerEnigma);
                $em->flush();
                return 2;

            default :
                return 1;
        }
    }
}