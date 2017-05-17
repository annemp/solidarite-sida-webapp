<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping\GeneratedValue;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @GeneratedValue
     * @ORM\Column(name="id_benevole", type="integer")
     */
    public $idBenevole;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    public $firstname;

    /**
     * @Assert\DateTime()
     *
     * @ORM\Column(name="birthdate", type="date")
     */
    public $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="email_user", type="string", length=255)
     */
    protected $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="driver_licence", type="boolean")
     */
    public $driverLicence;

    /**
     * @return bool
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=10)
     */
    public $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="text")
     */
    public $adress;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=5)
     */
    public $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    public $city;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="integer")
     */
    public $role;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /** Getters & Setters */
    /**
     * @return Assert\DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * @param Assert\DateTime $birthdate
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    /**
     * @return string
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * @param string $adress
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;
    }

    /**
     * @return string
     */
    public function getIdAiw()
    {
      return $this->IdAiw;
    }
  
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getIdBenevole()
    {
        return $this->idBenevole;
    }

    /**
     * @param mixed $idBenevole
     */
    public function setIdBenevole($idBenevole)
    {
        $this->idBenevole = $idBenevole;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }/**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isDriverLicence()
    {
        return $this->driverLicence;
    }

    /**
     * @param bool $driverLicence
     */
    public function setDriverLicence($driverLicence)
    {
        $this->driverLicence = $driverLicence;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }


}