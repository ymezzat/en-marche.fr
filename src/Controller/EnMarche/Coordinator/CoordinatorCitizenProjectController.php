<?php

namespace AppBundle\Controller\EnMarche\Coordinator;

use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\CitizenProject;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Form\CoordinatorAreaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Dimitri Gritsajuk <dimitri.gritsajuk@sensiolabs.com>
 *
 * @Route("/espace-coordinateur/projet-citoyen")
 * @Security("has_role('ROLE_COORDINATOR_CITIZEN_PROJECT')")
 */
class CoordinatorCitizenProjectController extends Controller
{
    /**
     * @Route(path="/list", name="app_coordinator_citizen_project")
     */
    public function listAction(Request $request)
    {
        try {
            $filters = CitizenProjectFilter::fromQueryString($request);
        } catch (\UnexpectedValueException $e) {
            throw new BadRequestHttpException('Unexpected Citizen Project status in the query string.', $e);
        }

        $results = $this->get('app.citizen_project.manager')->getCoordinatorCitizenProjects($this->getUser(), $filters);

        $forms = [];
        array_walk($results, function (CitizenProject $project) use (&$forms, $filters) {
            $forms[$project->getId()] = $this
                ->createForm(CoordinatorAreaType::class, $project, [
                    'data_class' => CitizenProject::class,
                    'action' => $this->generateUrl('app_coordinator_citizen_project_validate', [
                        'uuid' => $project->getUuid(),
                        'slug' => $project->getSlug(),
                    ]),
                    'status' => $filters->getStatus(),
                ])
                ->createView();
        });

        return $this->render('coordinator/citizen_project.html.twig', [
            'results' => $results,
            'filters' => $filters,
            'forms' => $forms,
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/pre-valider", name="app_coordinator_citizen_project_validate")
     * @Method("POST")
     */
    public function validateAction(Request $request, CitizenProject $project): Response
    {
        $form = $this
            ->createForm(CoordinatorAreaType::class, $project, [
                'data_class' => CitizenProject::class,
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('refuse')->isClicked()) {
                    $this->get('app.citizen_project.authority')->preRefuse($project);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                }

                if ($form->get('accept')->isClicked()) {
                    $this->get('app.citizen_project.authority')->preApprove($project);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                }
            } catch (BaseGroupException $exception) {
                throw $this->createNotFoundException(sprintf('Citizen project %u has already been treated by an administrator.', $project->getId()), $exception);
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error_'.$form->getData()->getId(), $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_coordinator_citizen_project', ['s' => CitizenProject::PENDING]);
    }
}
