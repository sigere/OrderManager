<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Task;
use App\Form\AddTaskForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private $entityManager;
    private $request;

    public function __construct(EntityManagerInterface $em, RequestStack $request)
    {
        $this->entityManager = $em;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @Route("/tasks", name="tasks")
     */
    public function index(): Response
    {
        $tasks = $this->loadTasks();
        return new Response("ok",200);
        return $this->render('tasks/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    private function loadTasks(): array
    {
        return $this->entityManager
            ->getRepository(Task::class)
            ->createQueryBuilder("t")
            ->andWhere("t.deletedAt is null")
            ->orderBy("t.deadline","ASC")
            ->getQuery()
            ->getResult();
    }

    /**
     * @Route("/tasks/api/addTask", name="tasks_api_addTask")
     * @return Response
     */
    public function addTask(): Response
    {
        $form = $this->createForm(AddTaskForm::class);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setAuthor($this->getUser());
            $this->entityManager->persist($task);
            $this->entityManager->persist(new Log($this->getUser(), "Dodano zadanie", $task));
            $this->entityManager->flush();
            return new Response("Dodano zadanie.", 201, ['task_id' => $task->getId()]);
        }

        return $this->render('task/addTask.html.twig', [
            'addTaskForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tasks/api/setDone/{id}", name="tasks_api_setDone")
     * @param Task $task
     * @return Response
     */
    public function setDone(Task $task): Response
    {
        if($task->getDoneAt())
            return new Response("Zadanie jest już wykonane.", 406);
        $task->setDoneAt(new \DateTime());
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return new Response("Wykonano zadanie", 200);
    }

    /**
     * @Route("/tasks/api/delete/{id}", name="tasks_api_delete")
     * @param Task $task
     * @return Response
     */
    public function delete(Task $task): Response
    {
        if($task->getDeletedAt())
            return new Response("Zadanie zostało juz usunięte.", 406);
        $task->setDeletedAt(new \DateTime());
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return new Response("Usunieto zadanie.", 200);
    }
}
