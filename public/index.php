<?php
/**
 * Created by PhpStorm.
 * User: alexc
 * Date: 30/01/2019
 * Time: 09:10
 */

define("ROOT", dirname(__DIR__));
require ROOT . "/vendor/autoload.php";

$app = new \Slim\App([
    'displayErrorDetails' => true
]);

$container = $app->getContainer();

$container['db'] = function ($container) {
    return new \PDO(
        "mysql:host=127.0.0.1;dbname=todolist;charset=utf8",
        "root",
        "root",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(ROOT . "/templates", []);
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

$app->add(function(\Slim\Http\Request $request, \Slim\Http\Response $response, $next) {

    $token = trim(stripcslashes(htmlentities($request->getParam('token'))));
    $state = false;

    if (isset($token) && $token === "843n6iNmfBnM423DTjM3H4a7wNt3QuGe") {
        $state = true;
    } else {
        $state = false;
    }

    $response = $next($request, $response);

    if ($state) {
        return $response;
    } else {
        return $response->withJson([
            "error" => "Token is invalid or not entered.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

});

$app->get("/persons", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("SELECT * FROM persons");
    $stmt->execute();
    $persons = $stmt->fetchAll();
    return $res->withJson($persons);
})->setName("persons_show_all");

$app->get("/persons/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("SELECT * FROM persons WHERE id = {$args['id']}");
    $stmt->execute();
    $persons = $stmt->fetchAll();
    return $res->withJson($persons);
})->setName("persons_show_one");

$app->post("/persons/new", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $person = trim(stripcslashes(htmlentities($req->getParam('person'))));
    if (empty($person)) {
        return $res->withJson([
            "error" => "Person parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    $stmt = $this->db->prepare("INSERT INTO persons(person) VALUES(:person)");
    $stmt->execute([
        "person" => $person
    ]);
    $persons = $stmt->fetchAll();
    return $res->withJson([
        "success" => "Your person is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("persons_add");

$app->put("/persons/update/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $person = trim(stripcslashes(htmlentities($req->getParam('person'))));
    if (empty($person)) {
        return $res->withJson([
            "error" => "Person parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    $stmt = $this->db->prepare("UPDATE persons SET person = :person WHERE id = :id");
    $stmt->execute([
        "person" => $person,
        "id" => $args['id']
    ]);
    $persons = $stmt->fetchAll();
    return $res->withJson([
        "success" => "Your person is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("persons_update");

$app->delete("/persons/delete/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("DELETE FROM persons WHERE id = :id");
    $stmt->execute([
        "id" => $args['id']
    ]);
    $persons = $stmt->fetchAll();
    return $res->withJson([
        "success" => "Your person is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("persons_delete");

$app->get("/tasks", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("SELECT tasks.*, persons.person FROM tasks INNER JOIN persons ON tasks.user_id = persons.id");
    $stmt->execute();
    $tasks = $stmt->fetchAll();
    return $res->withJson($tasks);
})->setName("tasks_show_all");

$app->get("/tasks/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("SELECT tasks.*, persons.person FROM tasks INNER JOIN persons ON tasks.user_id = persons.id WHERE tasks.id = {$args['id']}");
    $stmt->execute();
    $tasks = $stmt->fetchAll();
    return $res->withJson($tasks);
})->setName("tasks_show_one");

$app->post("/tasks/new", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $name = trim(stripcslashes(htmlentities($req->getParam('name'))));
    $task_at = trim(stripcslashes(htmlentities($req->getParam('date'))));
    $person = trim(stripcslashes(htmlentities($req->getParam('person'))));
    if (empty($name)) {
        return $res->withJson([
            "error" => "Name parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    if (empty($task_at)) {
        return $res->withJson([
            "error" => "Date parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    if (empty($person)) {
        return $res->withJson([
            "error" => "Person parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    $stmt = $this->db->prepare("INSERT INTO tasks(name, task_at, user_id) VALUES(:name, :task_at, :user_id)");
    $stmt->execute([
        "name" => $name,
        "task_at" => $task_at,
        "user_id" => $person
    ]);
    return $res->withJson([
        "success" => "Your task is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("tasks_add");

$app->put("/tasks/update/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $name = trim(stripcslashes(htmlentities($req->getParam('name'))));
    $task_at = trim(stripcslashes(htmlentities($req->getParam('date'))));
    $person = trim(stripcslashes(htmlentities($req->getParam('person'))));
    if (empty($name)) {
        return $res->withJson([
            "error" => "Name parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    if (empty($task_at)) {
        return $res->withJson([
            "error" => "Date parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    if (empty($person)) {
        return $res->withJson([
            "error" => "Person parameter is not correct.",
            "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }
    $stmt = $this->db->prepare("UPDATE tasks SET name = :name, task_at = :task_at, user_id = :user_id WHERE id = :id");
    $stmt->execute([
        "name" => $name,
        "task_at" => $task_at,
        "user_id" => $person,
        "id" => $args['id']
    ]);
    return $res->withJson([
        "success" => "Your task is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("tasks_update");

$app->delete("/tasks/delete/{id:[0-9]+}", function (\Slim\Http\Request $req, \Slim\Http\Response $res, $args) {
    $res->withHeader('Content-Type', 'application/json; charset=UTF-8');
    $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([
        "id" => $args['id']
    ]);
    return $res->withJson([
        "success" => "Your task is saved.",
        "executed_at" => (new DateTime())->format('Y-m-d H:i:s')
    ]);
})->setName("tasks_delete");

$app->run();
