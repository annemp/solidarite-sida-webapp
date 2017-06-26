<?php
namespace PeopleBundle\Controller;

use EventBundle\Entity\Team;
use EventBundle\Entity\Zone;
use PeopleBundle\Entity\People;
use PeopleBundle\Helper\RoleHelper;
use PeopleBundle\Importer\CSVImporter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PeopleController extends Controller
{
    /**
     * @Route("/login", name="account_login")
     * @Method({ "POST" })
     */
    public function loginAction(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        try {
            $user = $this->container->get('AuthenticationService')->login($email, $password);
            $request->getSession()->set('user.email', $user->getEmail());
            return $this->redirectToRoute('dashboard');
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/", name="homepage")
     * @Method({ "GET" })
     */
    public function homepageAction(Request $request)
    {
        if ($this->get('CurrentUser')->isAuthenticated()) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('@PeopleBundle/homepage.html.twig');
    }

    /**
     * @Route("/import", name="import")
     * @Method({ "GET" })
     */
    public function importAction(Request $request)
    {
        if (!$this->get('CurrentUser')->isAuthenticated()) {
            return $this->redirectToRoute('homepage');
        }

        if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::VOLONTARIA) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('@EventBundle/import.html.twig');
    }

    /**
     * @Route("/importing", name="importing")
     * @Method({ "POST" })
     */
    public function importingAction(Request $request)
    {
        if (!$this->get('CurrentUser')->isAuthenticated()) {
            return $this->redirectToRoute('homepage');
        }

        if (!$this->get('CurrentUser')->get()->isAdmin()) {
            return $this->redirectToRoute('dashboard');
        }

        $csvFile = $request->files->get('csvFile');
        $people = CSVImporter::import($csvFile->getRealPath());
        $em = $this->getDoctrine()->getEntityManager();

        foreach($people as $person) {
            // Check if the zone already exists
            $zoneEntry = $this->get('ZoneRepository')->getFromName($person['zone']);
            if (!$zoneEntry) {
                $newZone = new Zone();
                $newZone->setName($person['zone']);
                $em->persist($newZone);
                $em->flush();
            } else {
                $newZone = $zoneEntry[0];
            }

            // Check if the team already exists
            $teamEntry = $this->get('TeamRepository')->getFromNameAndZone($person['team'], $newZone->getId());
            if (!$teamEntry) {
                $newTeam = new Team();
                $newTeam->setName($person['team']);
                $newTeam->setZone($newZone);
                $em->persist($newTeam);
                $em->flush();
            } else {
                $newTeam = $teamEntry[0];
            }

            // Check if the user already exists
            $databaseEntry = $this->get('PeopleRepository')->getFromEmail($person['email']);
            if (!$databaseEntry) {
                $newPerson = new People();
                $newPerson->setEmail($person['email']);
            } else {
                $newPerson = $databaseEntry;
            }

            // Update user informations
            $newPerson->setFirstName($person['firstName']);
            $newPerson->setLastName($person['lastName']);
            $newPerson->setAddress($person['address']);
            $newPerson->setZipcode($person['zipcode']);
            $newPerson->setCity($person['city']);
            $newPerson->setPhone($person['phone']);
            $newPerson->setDriverLicense($person['driverLicense']);
            $newPerson->setTeam($newTeam);

            // SAVE CHANGES
            $em->persist($newPerson);
            $em->flush();
        }

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/print", name="print")
     * @Method({ "GET" })
     */
    public function printAction()
    {
        if (!$this->get('CurrentUser')->isAuthenticated()) {
            return $this->redirectToRoute('homepage');
        }

        if (
            $this->get('CurrentUser')->get()->getRole() !== RoleHelper::VOLONTARIA &&
            $this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_TEAM &&
            $this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_POLE &&
            $this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_SUBTEAM

        ) {
            return $this->redirectToRoute('dashboard');
        }

        if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::VOLONTARIA){
            // Here we get all the zones coordinators
            // Only display zones where the current user is the chief if he is zone chief
            if ($this->get('CurrentUser')->get()->getRole() === RoleHelper::COORDINATOR) {
                $ids = $this->get('ZoneHasChiefRepository')->findBy(['people_id' => $this->get('CurrentUser')->get()->getId()]);
                $ids = array_reduce($ids, function ($previous, $zone) {
                    $previous[] = $zone->getZoneId();
                    return $previous;
                }, []);
                $zones = $this->get('ZoneRepository')->findBy(['id' => $ids]);
            } else {
                $zones = $this->get('zoneRepository')->findAll();
            }
            return $this->render('@EventBundle/print.html.twig', [
                'zones' => $zones,

            ]);

        } else if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::COORDINATOR){
            // Here we get all the teams chiefs in a same zone
            if ($this->get('CurrentUser')->get()->getRole() === RoleHelper::COORDINATOR) {
                $ids = $this->get('ZoneHasChiefRepository')->findBy(['people_id' => $this->get('CurrentUser')->get()->getId()]);
                $ids = array_reduce($ids, function ($previous, $zone) {
                    $previous[] = $zone->getZoneId();
                    return $previous;
                }, []);
                $zones = $this->get('ZoneRepository')->findBy(['id' => $ids]);
            } else {
                $zones = $this->get('zoneRepository')->findAll();
            }
            return $this->render('@EventBundle/print.html.twig', [
                'zones' => $zones,

            ]);
        } else if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_TEAM){
            // Here we get all the volunteers in the team ordered by teams

            if ($this->get('CurrentUser')->get()->getRole() === RoleHelper::CHIEF_TEAM) {
                $ids = $this->get('TeamHasChiefRepository')->findBy(['people_id' => $this->get('CurrentUser')->get()->getId()]);
                $ids = array_reduce($ids, function ($previous, $team) {
                    $previous[] = $team->getTeamId();
                    return $previous;
                }, []);
                $teams = $this->get('TeamRepository')->findBy(['id' => $ids]);
            } else {
                $teams = $this->get('TeamRepository')->findBy(['zone_id' => $zone]);
            }

            return $this->render('@EventBundle/print.html.twig', [
                'zoneId' => $zone,
                'teams'  => $teams,
            ]);

        } else if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_POLE){
            // Here we get all the volunteers in the teitam classed by pole

            if ($this->get('CurrentUser')->get()->getRole() === RoleHelper::CHIEF_POLE) {
                $ids = $this->get('PoleHasChiefRepository')->findBy(['people_id' => $this->get('CurrentUser')->get()->getId()]);
                $ids = array_reduce($ids, function ($previous, $pole) {
                    $previous[] = $pole->getPoleId();
                    return $previous;
                }, []);
                $poles = $this->get('PoleRepository')->findBy(['id' => $ids]);
            } else {
                $poles = $this->get('PoleRepository')->findBy(['team_id' => $team]);
            }

            return $this->render('@EventBundle/print.html.twig', [
                'teamId' => $team,
                'poles'  => $poles,
            ]);

        } else if ($this->get('CurrentUser')->get()->getRole() !== RoleHelper::CHIEF_SUBTEAM){
            // Here we get all the volunteers in the team ordered by subteam

            if ($this->get('CurrentUser')->get()->getRole() === RoleHelper::CHIEF_SUBTEAM) {
                $ids = $this->get('SubteamHasChiefRepository')->findBy(['people_id' => $this->get('CurrentUser')->get()->getId()]);
                $ids = array_reduce($ids, function ($previous, $subteam) {
                    $previous[] = $subteam->getSubteamId();
                    return $previous;
                }, []);
                $subteams = $this->get('subteamRepository')->findBy(['id' => $ids]);
            } else {
                $subteams = $this->get('subteamRepository')->findBy(['pole_id' => $pole]);
            }

            return $this->render('@EventBundle/print.html.twig', [
                'poleId'    => $pole,
                'subteams'  => $subteams,
            ]);

        }
    }
}
