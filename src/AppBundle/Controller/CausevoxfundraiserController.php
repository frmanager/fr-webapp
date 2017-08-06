<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Causevoxfundraiser;
use AppBundle\Entity\Grade;
use AppBundle\Entity\Student;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\ValidationHelper;

/**
 * Causevoxfundraiser controller.
 *
 * @Route("/manage/causevoxfundraiser")
 */
class CausevoxfundraiserController extends Controller
{
    /**
     * Lists all Causevoxfundraiser entities.
     *
     * @Route("/", name="causevoxfundraiser_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entity = 'Causevoxfundraiser';
        $em = $this->getDoctrine()->getManager();

        $causevoxfundraisers = $em->getRepository('AppBundle:'.$entity)->findAll();

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'causevoxfundraisers' => $causevoxfundraisers,
            'entity' => $entity,
        ));
    }

    /**
     * Creates a new Causevoxfundraiser entity.
     *
     * @Route("/new", name="causevoxfundraiser_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $entity = 'Causevoxfundraiser';
        $causevoxfundraiser = new Causevoxfundraiser();
        $form = $this->createForm('AppBundle\Form\CausevoxfundraiserType', $causevoxfundraiser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($causevoxfundraiser);
            $em->flush();

            return $this->redirectToRoute(strtolower($entity).'_show', array('id' => $causevoxfundraiser->getId()));
        }

        return $this->render('crud/new.html.twig', array(
            'causevoxfundraiser' => $causevoxfundraiser,
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Finds and displays a Causevoxfundraiser entity.
     *
     * @Route("/show/{id}", name="causevoxfundraiser_show")
     * @Method("GET")
     */
    public function showAction(Causevoxfundraiser $causevoxfundraiser)
    {
        $entity = 'Causevoxfundraiser';
        $deleteForm = $this->createDeleteForm($causevoxfundraiser);

        return $this->render(strtolower($entity).'/show.html.twig', array(
            'causevoxfundraiser' => $causevoxfundraiser,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Causevoxfundraiser entity.
     *
     * @Route("/edit/{id}", name="causevoxfundraiser_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Causevoxfundraiser $causevoxfundraiser)
    {
        $entity = 'Causevoxfundraiser';
        $deleteForm = $this->createDeleteForm($causevoxfundraiser);
        $editForm = $this->createForm('AppBundle\Form\CausevoxfundraiserType', $causevoxfundraiser);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($causevoxfundraiser);
            $em->flush();

            return $this->redirectToRoute(strtolower($entity).'_index', array('id' => $causevoxfundraiser->getId()));
        }

        return $this->render('crud/edit.html.twig', array(
            'causevoxfundraiser' => $causevoxfundraiser,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Deletes a Causevoxfundraiser entity.
     *
     * @Route("/delete/{id}", name="causevoxfundraiser_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Causevoxfundraiser $causevoxfundraiser)
    {
        $entity = 'Causevoxfundraiser';
        $form = $this->createDeleteForm($causevoxfundraiser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($causevoxfundraiser);
            $em->flush();
        }

        return $this->redirectToRoute(strtolower($entity).'_index');
    }

    /**
     * Creates a form to delete a Causevoxfundraiser entity.
     *
     * @param Causevoxfundraiser $causevoxfundraiser The Causevoxfundraiser entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Causevoxfundraiser $causevoxfundraiser)
    {
        $entity = 'Causevoxfundraiser';

        return $this->createFormBuilder()
            ->setAction($this->generateUrl(strtolower($entity).'_delete', array('id' => $causevoxfundraiser->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Upload multiple Causevoxfundraiser via CSV File.
     *
     * @Route("/upload", name="causevoxfundraiser_upload")
     * @Method({"GET", "POST"})
     */
    public function uploadForm(Request $request)
    {
        $logger = $this->get('logger');
        $entity = 'Causevoxfundraiser';
        $mode = 'update';
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
                $CSVHelper->getFirstNameFromFullName();

                $templateFields = array(
                  'stub',
                  'first_name',
                  'last_name',
                  'email',
                  'funds_raised',
                  'funds_needed',
                  'number_of_donations',
                  'teams',
                  'joined',
                  'grade',
                  'classrooms_name',
                  'students_name', );

                if ($CSVHelper->validateHeaders($templateFields)) {
                    $logger->info('Making changes to database');
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
                            'The '.$entity.' table has been truncated'
                        );
                    }

                    $logger->info('Uploading Data');
                    $em = $this->getDoctrine()->getManager();
                    $errorMessages = [];
                    $errorMessage;
                    foreach ($CSVHelper->getData() as $i => $item) {
                        $failure = false;
                        unset($studentId);
                        unset($errorMessage);
                        unset($student);
                        unset($grade);
                        unset($classroom);

                        if (!$failure) {
                            $grade = $this->getDoctrine()->getRepository('AppBundle:Grade')->findOneByName($item['grade']);
                            if (empty($grade)) {
                                $failure = true;
                                $errorMessage = new ValidationHelper(array(
                              'entity' => $entity,
                              'row_index' => ($i + 2),
                              'error_field' => 'grade',
                              'error_field_value' => $item['grade'],
                              'error_message' => 'Could not find grade for fundraiser: '.$item['email'],
                              'error_level' => ValidationHelper::$level_error, ));
                            }else{
                              $logger->debug("Found Grade #".$grade->getId().": ".$grade->getName());
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
                            }else{
                              $logger->debug("Found Classroom #".$classroom->getId().": ".$classroom->getClassroomName());
                            }
                        }

                        //Here is our find student logic. We try a lot of different methods to try and find it....
                        if (!$failure) {

                            if (!isset($studentId)) {
                                $queryString = sprintf("SELECT u.id FROM AppBundle:Student u WHERE u.classroom = '%s' AND u.name = '%s'", $classroom->getId(), $item['students_name']);
                                $result = $em->createQuery($queryString)->getResult();

                                if (!empty($result)) {
                                    $studentId = $result[0]['id'];
                                    $logger->debug('Row ['.($i + 2).'] - Found student "'.$item['students_name'].'" [#'.$studentId.'] using provided name');
                                }
                            }

                            if (!isset($studentId)) {
                                $queryString = sprintf("SELECT u.id FROM AppBundle:Student u WHERE u.classroom = '%s' AND u.name = '%s'", $classroom->getId(), $item['students_first_name']);
                                $result = $em->createQuery($queryString)->getResult();
                                if (!empty($result)) {
                                    $studentId = $result[0]['id'];
                                    $logger->debug('Row ['.($i + 2).'] - Found student "'.$item['students_name'].'" [#'.$studentId.'] using first name fuzzy match "'.$item['students_first_name'].'"');
                                }
                            }

                            if (!isset($studentId)) {
                                $queryString = sprintf("SELECT u.id FROM AppBundle:Student u WHERE u.classroom = '%s' AND u.name = '%s'", $classroom->getId(), $item['students_name_with_initial']);
                                $result = $em->createQuery($queryString)->getResult();

                                if (!empty($result)) {
                                    $studentId = $result[0]['id'];
                                    $logger->debug('Row ['.($i + 2).'] - Found student "'.$item['students_name'].'" [#'.$studentId.'] using first name + last initial fuzzy match "'.$item['students_name_with_initial'].'"');
                                }
                            }

                          //IF ALL ELSE FAILES, ITS A FAILURE
                          if (!isset($studentId)) {
                              $failure = true;
                              $errorMessage = new ValidationHelper(array(
                                  'entity' => $entity,
                                  'row_index' => ($i + 2),
                                  'error_field' => 'students_name, classroom, grade',
                                  'error_field_value' => $item['students_name'].', '.$item['classrooms_name'].', '.$item['grade'],
                                  'error_message' => 'Could not find student',
                                  'error_level' => ValidationHelper::$level_error, ));
                          }
                        } //END STUDENT FIND LOGIC


                        if (!$failure) {
                            $student = $em->find('AppBundle:Student', $studentId);
                            $logger->debug("Found Student #".$student->getId().": ".$student->getName());
                            $causevoxfundraiser = $this->getDoctrine()->getRepository('AppBundle:'.$entity)->findOneBy(
                        array('email' => $item['email'], 'student' => $student, 'classroom' => $classroom)
                        );
                        //Going to perform "Insert" vs "Update"
                          if (empty($causevoxfundraiser)) {
                              $logger->debug($entity.' not found. ['.$item['email'].' - '.$student->getName().' - '.$classroom->getClassroomName().'] .creating new record');
                              $causevoxfundraiser = new Causevoxfundraiser();
                              $causevoxfundraiser->setEmail($item['email']);
                          } else {
                              $logger->debug($entity.' found....updating existing record');
                              /*
                              $errorMessage = new ValidationHelper(array(
                                'entity' => $entity,
                                'row_index' => ($i + 2),
                                'error_field' => 'N/A',
                                'error_field_value' => 'N/A',
                                'error_message' => 'Duplicate with Causvox Donation #'.$causevoxfundraiser->getId(),
                                'error_level' => ValidationHelper::$level_warning, ));
                              */
                          }

                            $causevoxfundraiser->setFundsNeeded(intval($item['funds_needed']));
                            $causevoxfundraiser->setUrl($item['stub']);
                            $causevoxfundraiser->setFundsRaised(intval($item['funds_raised']));
                            $causevoxfundraiser->setStudent($student);
                            $causevoxfundraiser->setClassroom($classroom);
                            $causevoxfundraiser->setFirstName($item['first_name']);
                            $causevoxfundraiser->setLastName($item['last_name']);

                            $validator = $this->get('validator');
                            $errors = $validator->validate($causevoxfundraiser);

                            if (strcmp($mode, 'validate') !== 0) {
                                if (count($errors) > 0) {
                                    $errorsString = (string) $errors;
                                    $logger->error('[ROW #'.($i + 2).'] Could not add ['.$entity.']: '.$errorsString);
                                    $this->addFlash(
                                        'danger',
                                        '[ROW #'.($i + 2).'] Could not add ['.$entity.']: '.$errorsString
                                    );
                                } else {
                                    $em->persist($causevoxfundraiser);
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
                          'file_type' => $entity
                      ));
                    }
                } else {
                    $logger->info('file does not have mandatory fields ['.implode(', ', $templateFields).']. Please verify it was downloaded from Causevox');
                    $logger->info('File was not a .csv');
                    $this->addFlash(
                        'danger',
                        'file does not have mandatory fields ['.implode(', ', $templateFields).']. Please verify it was downloaded from Causevox'
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
