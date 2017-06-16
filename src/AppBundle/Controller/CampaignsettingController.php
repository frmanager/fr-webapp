<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Campaignsetting;

/**
 * Campaignsetting controller.
 *
 * @Route("/manage/campaignsetting")
 */
class CampaignsettingController extends Controller
{
    /**
     * Lists all Campaignsetting entities.
     *
     * @Route("/", name="campaignsetting_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entity = 'Campaignsetting';
        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
        $campaignsettings = $em->getRepository('AppBundle:Campaignsetting')->findAll();

        if (empty($campaignsettings)) {
            $defaultCampaignSettings = [];

            array_push($defaultCampaignSettings, array('displayName' => 'campaign_start_date', 'value' => '9/15/'.date('Y'), 'format' => 'mm/dd/yyyy'));
            array_push($defaultCampaignSettings, array('displayName' => 'campaign_end_date', 'value' => '10/27/'.date('Y'), 'format' => 'mm/dd/yyyy'));
            array_push($defaultCampaignSettings, array('displayName' => 'campaign_funding_goal', 'value' => '20000', 'format' => 'an Amount, no commas!'));
            array_push($defaultCampaignSettings, array('displayName' => 'campaign_url', 'value' => 'http://funrun.lrespto.org', 'format' => 'FQDN'));

            foreach ($defaultCampaignSettings as $defaultCampaignSetting) {
                $em = $this->getDoctrine()->getManager();

                $campaignsetting = new Campaignsetting();
                $campaignsetting->setDisplayName($defaultCampaignSetting['displayName']);
                $campaignsetting->setFormat($defaultCampaignSetting['format']);
                $campaignsetting->setValue($defaultCampaignSetting['value']);

                $em->persist($campaignsetting);
                $em->flush();
            }

            $em->clear();

            $campaignsettings = $em->getRepository('AppBundle:Campaignsetting')->findAll();

            $this->addFlash(
              'info',
              'Default Campaignsettings Added'
            );
        }

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'campaignsettings' => $campaignsettings,
            'entity' => $entity,
            'campaign' => $campaign
        ));
    }

    /**
     * Creates a new Campaignsetting entity.
     *
     * @Route("/new", name="campaignsetting_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $entity = 'Campaignsetting';
        $campaignsetting = new Campaignsetting();
        $form = $this->createForm('AppBundle\Form\CampaignsettingNewType', $campaignsetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($campaignsetting);
            $em->flush();

            return $this->redirectToRoute('campaignsetting_index', array('id' => $campaignsetting->getId()));
        }

        return $this->render('crud/new.html.twig', array(
            'campaignsetting' => $campaignsetting,
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Finds and displays a Campaignsetting entity.
     *
     * @Route("/show/{id}", name="campaignsetting_show")
     * @Method("GET")
     */
    public function showAction(Campaignsetting $campaignsetting)
    {
        $entity = 'Campaignsetting';
        $deleteForm = $this->createDeleteForm($campaignsetting);

        return $this->render(strtolower($entity).'/show.html.twig', array(
            'campaignsetting' => $campaignsetting,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Campaignsetting entity.
     *
     * @Route("/edit/{id}", name="campaignsetting_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Campaignsetting $campaignsetting)
    {
        $entity = 'Campaignsetting';
        $deleteForm = $this->createDeleteForm($campaignsetting);
        $editForm = $this->createForm('AppBundle\Form\CampaignsettingEditType', $campaignsetting);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($campaignsetting);
            $em->flush();

            $this->addFlash(
              'success',
              'Campaignsettings Saved!'
            );

            return $this->redirectToRoute('campaignsetting_index', array('id' => $campaignsetting->getId()));
        }

        return $this->render('crud/edit.html.twig', array(
            'campaignsetting' => $campaignsetting,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Deletes a Campaignsetting entity.
     *
     * @Route("/delete/{id}", name="campaignsetting_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Campaignsetting $campaignsetting)
    {
        $entity = 'Campaignsetting';
        $form = $this->createDeleteForm($campaignsetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($campaignsetting);
            $em->flush();
        }

        return $this->redirectToRoute('campaignsetting_index');
    }

    /**
     * Creates a form to delete a Campaignsetting entity.
     *
     * @param Campaignsetting $campaignsetting The Campaignsetting entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Campaignsetting $campaignsetting)
    {
        $entity = 'Campaignsetting';

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('campaignsetting_delete', array('id' => $campaignsetting->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
