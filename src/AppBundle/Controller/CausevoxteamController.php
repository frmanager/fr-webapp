<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Causevoxteam;
use AppBundle\Entity\Grade;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\ValidationHelper;

/**
 * Causevoxteam controller.
 *
 * @Route("/manage/causevoxteam")
 */
class CausevoxteamController extends Controller
{
    /**
     * Lists all Causevoxteam entities.
     *
     * @Route("/", name="causevoxteam_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entity = 'Causevoxteam';
        $em = $this->getDoctrine()->getManager();

        $causevoxteams = $em->getRepository('AppBundle:'.$entity)->findAll();

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'causevoxteams' => $causevoxteams,
            'entity' => $entity,
        ));
    }

    /**
     * Creates a new Causevoxteam entity.
     *
     * @Route("/new", name="causevoxteam_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $entity = 'Causevoxteam';
        $causevoxteam = new Causevoxteam();
        $form = $this->createForm('AppBundle\Form\UploadType', array('entity' => $entity, 'file_type' => $entity));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($causevoxteam);
            $em->flush();

            return $this->redirectToRoute(strtolower($entity).'_show', array('id' => $causevoxteam->getId()));
        }

        return $this->render('crud/new.html.twig', array(
            'causevoxteam' => $causevoxteam,
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Finds and displays a Causevoxteam entity.
     *
     * @Route("/show/{id}", name="causevoxteam_show")
     * @Method("GET")
     */
    public function showAction(Causevoxteam $causevoxteam)
    {
        $entity = 'Causevoxteam';
        $deleteForm = $this->createDeleteForm($causevoxteam);

        return $this->render(strtolower($entity).'/show.html.twig', array(
            'causevoxteam' => $causevoxteam,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Causevoxteam entity.
     *
     * @Route("/edit/{id}", name="causevoxteam_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Causevoxteam $causevoxteam)
    {
        $entity = 'Causevoxteam';
        $deleteForm = $this->createDeleteForm($causevoxteam);
        $editForm = $this->createForm('AppBundle\Form\CausevoxteamType', $causevoxteam);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($causevoxteam);
            $em->flush();

            return $this->redirectToRoute(strtolower($entity).'_edit', array('id' => $causevoxteam->getId()));
        }

        return $this->render('crud/edit.html.twig', array(
            'causevoxteam' => $causevoxteam,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Deletes a Causevoxteam entity.
     *
     * @Route("/delete/{id}", name="causevoxteam_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Causevoxteam $causevoxteam)
    {
        $entity = 'Causevoxteam';
        $form = $this->createDeleteForm($causevoxteam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($causevoxteam);
            $em->flush();
        }

        return $this->redirectToRoute(strtolower($entity).'_index');
    }

    /**
     * Creates a form to delete a Causevoxteam entity.
     *
     * @param Causevoxteam $causevoxteam The Causevoxteam entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Causevoxteam $causevoxteam)
    {
        $entity = 'Causevoxteam';

        return $this->createFormBuilder()
            ->setAction($this->generateUrl(strtolower($entity).'_delete', array('id' => $causevoxteam->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Upload multiple Causevoxteams via CSV File.
     *
     * @Route("/upload", name="causevoxteam_upload")
     * @Method({"GET", "POST"})
     */
    public function uploadForm(Request $request)
    {
        $logger = $this->get('logger');
        $entity = 'Causevoxteam';
        $mode = 'update';
        $logger->debug("My Role: ".print_r($this->getUser()->getRoles(), true));
        $form = $this->createForm('AppBundle\Form\UploadType', array('entity' => $entity, 'file_type' => $entity, 'role' => $this->getUser()->getRoles()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null != $form['upload_mode']->getData()) {
                $mode = $form['upload_mode']->getData();
            } else {
                $logger->error('No mode was selected. defaulted to update');
            }

            $uploadFile = $form['attachment']->getData();

            if (strpos($uploadFile->getClientOriginalName(), '.csv') !== false) {
                $logger->info('File was a .csv, attempting to load');
                $uploadFile->move('temp/', strtolower($entity).'.csv');
                $CSVHelper = new CSVHelper();
                $CSVHelper->processFile('temp/', strtolower($entity).'.csv');
                $CSVHelper->cleanClassroomNames();

                $templateFields = array('name', 'grade', 'campaignUrl', 'funds_needed', 'funds_raised', 'classrooms_name', 'members', 'admins');

                if ($CSVHelper->validateHeaders($templateFields)) {
                    $logger->debug('Making changes to database');
                    $em = $this->getDoctrine()->getManager();

                    if (strcmp($mode, 'truncate') == 0) {
                        $logger->info('User selected to [truncate] table');

                        $qb = $em->createQueryBuilder();
                        $qb->delete('AppBundle:'.$entity, 's');
                        $query = $qb->getQuery();

                        $query->getResult();

                        $em->flush();

                        $this->addFlash(
                            'info',
                            'The Causevox Teams table has been truncated'
                        );
                    }

                    $logger->info('Uploading Data');
                    $em = $this->getDoctrine()->getManager();
                    $errorMessages = [];
                    $errorMessage;
                    foreach ($CSVHelper->getData() as $i => $item) {
                        $failure = false;
                        unset($errorMessage);

                        if (!$failure) {
                            $grade = $this->getDoctrine()->getRepository('AppBundle:Grade')->findOneByName($item['grade']);
                            if (empty($grade)) {
                                $failure = true;
                                $errorMessage = new ValidationHelper(array(
                                'entity' => $entity,
                                'row_index' => ($i + 2),
                                'error_field' => 'grade',
                                'error_field_value' => $item['grade'],
                                'error_message' => 'Could not find grade',
                                'error_level' => ValidationHelper::$level_error, ));
                            }
                        }

                        if (!$failure) {
                            $classroom = $this->getDoctrine()->getRepository('AppBundle:Classroom')->findOneByClassroomName($item['classrooms_name']);
                            if (empty($classroom)) {
                                $failure = true;
                                $errorMessage = new ValidationHelper(array(
                                'entity' => $entity,
                                'row_index' => ($i + 2),
                                'error_field' => 'classrooms_name',
                                'error_field_value' => $item['classrooms_name'],
                                'error_message' => 'Could not find classroom',
                                'error_level' => ValidationHelper::$level_error, ));
                            }
                        }

                        if (!$failure) {
                            $causevoxteam = $this->getDoctrine()->getRepository('AppBundle:'.$entity)->findOneBy(
                          array('classroom' => $classroom)
                          );
                          //Going to perform "Insert" vs "Update"
                            if (empty($causevoxteam)) {
                                $logger->debug($entity.' not found....creating new record');
                                $causevoxteam = new Causevoxteam();
                            } else {
                                $logger->debug($entity.' found....updating existing record');
                                /*
                                $errorMessage = new ValidationHelper(array(
                                  'entity' => $entity,
                                  'row_index' => ($i + 2),
                                  'error_field' => 'N/A',
                                  'error_field_value' => 'N/A',
                                  'error_message' => 'Duplicate with Causvox Team #'.$causevoxteam->getId(),
                                  'error_level' => ValidationHelper::$level_warning, ));
                                */
                            }

                            $causevoxteam->setName($item['name']);
                            $causevoxteam->setFundsNeeded(intval($item['funds_needed']));
                            $causevoxteam->setUrl($item['campaignUrl']);
                            $causevoxteam->setFundsRaised(intval($item['funds_raised']));
                            $causevoxteam->setClassroom($classroom);

                            $validator = $this->get('validator');
                            $errors = $validator->validate($causevoxteam);

                            if (strcmp($mode, 'validate') !== 0) {
                                if (count($errors) > 0) {
                                    $errorsString = (string) $errors;
                                    $logger->error('[ROW #'.($i + 2).'] Could not add ['.$entity.']: '.$errorsString);
                                    $this->addFlash(
                                        'danger',
                                        '[ROW #'.($i + 2).'] Could not add ['.$entity.']: '.$errorsString
                                    );
                                } else {
                                    $em->persist($causevoxteam);
                                    $em->flush();
                                    $em->clear();
                                }
                            } //Otherwise we do Nothing....
                        }

                        if (isset($errorMessage) && strcmp($mode, 'validate') !== 0) {
                            $this->addFlash(
                                  $errorMessage->getErrorLevel(),
                                  $errorMessage->printFlashBagMessage()
                              );
                        }

                        //Push Error Message
                        if (isset($errorMessage)) {
                            array_push($errorMessages, $errorMessage->getMap());
                        }
                    }

                    if (strcmp($mode, 'validate') !== 0) {
                        $em->flush();
                        $em->clear();

                        return $this->redirectToRoute(strtolower($entity).'_index');
                    } else {
                        return $this->render('crud/validate.html.twig', array(
                          'error_messages' => $errorMessages,
                          'entity' => $entity,
                      ));
                    }
                } else {
                    $logger->info('file does not have mandatory fields. Please verify it was downloaded from Causevox');
                    $logger->info('File was not a .csv');
                    $this->addFlash(
                        'danger',
                        'file does not have mandatory fields. Please verify it was downloaded from Causevox'
                    );
                }
            } else {
                $logger->info('File was not a .csv');
                $this->addFlash(
                    'danger',
                    'File was not a .csv'
                );
            }
        }

        return $this->render('crud/upload.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'file_type' => $entity,
        ));
    }
}
