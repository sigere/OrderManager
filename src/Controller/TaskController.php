<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Task;
use App\Form\AddTaskForm;
use App\Form\DeleteEntityFrom;
use App\Repository\LogRepository;
use App\Repository\TaskRepository;
use App\Service\OptionsProviderFactory;
use App\Service\ResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tasks")
 */
class TaskController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository $taskRepository,
        private LogRepository $logRepository,
        private ResponseFormatter $formatter,
        private OptionsProviderFactory $optionsProviderFactory
    ) {
    }

    /**
     * @Route("/", methods={"GET"}, name="tasks")
     */
    public function index(Request $request): Response
    {
        $tasks = $this->taskRepository->getByTasksPreferences();
        $task = $this->taskRepository->findOneBy(['id' => $request->get('task')]);
        $logs = $this->logRepository->findBy(
            ['task' => $task],
            ['createdAt' => 'DESC'],
            100
        );
        $options = $task ? $this->optionsProviderFactory->getOptions($task) : [];

        return $this->render('tasks/index.html.twig', [
            'tasks' => $tasks,
            'details' => [
                'task' => $task,
                'logs' => $logs
            ],
            'options' => $options,
            'dataSourceUrl' => "/tasks/task"
        ]);
    }

    /**
     * @Route("/task", methods={"GET"}, name="tasks_task_get_all")
     */
    public function getTasks(): Response
    {
        return $this->render('tasks/tasks_table.html.twig', [
            'tasks' => $this->taskRepository->getByTasksPreferences(),
            'dataSourceUrl' => "/tasks/task"
        ]);
    }

    /**
     * @Route("/task/{id}", methods={"GET"}, name="tasks_task_get")
     */
    public function getTask(Task $task): Response
    {
        $logs = $this->logRepository->findBy(
            ['task' => $task],
            ['createdAt' => 'DESC'],
            100
        );
        $options = $this->optionsProviderFactory->getOptions($task);

        $result = [];
        $result['details'] = $this->renderView('tasks/details.html.twig', [
            'task' => $task,
            'logs' => $logs,
        ]);

        $result['burger'] = $this->renderView('burger.html.twig', [
            'options' => $options
        ]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/task", methods={"POST"}, name="tasks_task_post")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(AddTaskForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setAuthor($this->getUser());
            $this->entityManager->persist($task);
            $this->entityManager->persist(new Log($this->getUser(), 'Dodano zadanie', $task));
            $this->entityManager->flush();

            return new Response($this->formatter->success('Dodano zadanie.'), 201, ['task_id' => $task->getId()]);
        }

        return $this->render('tasks/task_form.html.twig', [
            'addTaskForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/task/{id}", methods={"PUT"}, name="tasks_task_put")
     */
    public function update(Request $request, Task $task): Response
    {
        $attr = array_merge(AddTaskForm::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/tasks/task/' . $task->getId(),
            'data-method' => 'PUT'
        ]);
        $options = array_merge(AddTaskForm::DEFAULT_OPTIONS, [
            'attr' => $attr,
            'method' => 'PUT'
        ]);

        $form = $this->createForm(AddTaskForm::class, $task, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $this->entityManager->persist($task);
            $this->entityManager->persist(new Log($this->getUser(), 'Zaktualizowano zadanie', $task));
            $this->entityManager->flush();

            return new Response($this->formatter->success('Zaktualizowano zadanie.'), 201);
        }

        return $this->render('tasks/task_form.html.twig', [
            'addTaskForm' => $form->createView(),
            'update' => true
        ]);
    }

    /**
     * @Route("/task/{id}", methods={"DELETE"}, name="tasks_task_delete")
     */
    public function delete(Request $request, Task $task): Response
    {
        if ($task->getDeletedAt()) {
            return new Response(
                $this->formatter->notice('Zadanie zostało już usunięte.'),
                406
            );
        }

        $attr = array_merge(DeleteEntityFrom::DEFAULT_OPTIONS['attr'] ?? [], [
            'data-url' => '/tasks/task/' . $task->getId(),
        ]);
        $options = array_merge(DeleteEntityFrom::DEFAULT_OPTIONS, ['attr' => $attr]);
        $form = $this->createForm(DeleteEntityFrom::class, null, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setDeletedAt(new \Datetime());
            $this->entityManager->persist($task);
            $this->entityManager->persist(new Log($this->getUser(), 'Usunięto zadanie', $task));
            $this->entityManager->flush();

            return new Response(
                $this->formatter->success('Zadanie usunięte.'),
                200
            );
        }

        return $this->render('delete_entity_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
