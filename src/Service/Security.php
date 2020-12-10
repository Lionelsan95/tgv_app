<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class Security
{
    private $em;
    private $mailer;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function recoverPassword(User $user):void
    {
        $user->setEnabled(false);
        $user->setRecoverHash($this->recoverCode($user->getEmail()));
        $this->em->flush();
    }

    public function sendMail($data)
    {
        $message = (new \Swift_Message($data['subject']))
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setBody(
                $data["body"],
                "text/html");

        try
        {
            $this->mailer->send($message);
        }catch (\Exception $e)
        {
            dump($e->getMessage());
            throw new \Exception();
        }
    }


    public function recoverCode(String $email){

        srand(986534);
        $part_one = str_shuffle('0123456789');

        srand(98853694126534);
        $part_two = str_shuffle($email);

        return $this->removeSpecialChar(password_hash(str_shuffle($part_one.$part_two), PASSWORD_BCRYPT, [
            'cost' => 12
        ]));
    }

    public function removeSpecialChar($str) {

        return str_replace(
            ['\'',
                '"',
                '/',
                '&',
                '=',
                ',' ,
                ';',
                '<',
                '>'
            ], ' ', $str);
    }

    public function changePassword(User $user, $password, $oldpassword=""): bool
    {
        $bool = false;
        if($user)
        {
            $bool = $oldpassword == "" ? true : $this->passwordEncoder->isPasswordValid($user, $oldpassword);
            if($bool)
            {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
                $user->setEnabled(true);
                $user->setRecoverHash("");
                $this->em->flush();
            }
        }
        return $bool;
    }
}