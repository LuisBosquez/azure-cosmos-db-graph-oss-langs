<?php
require_once('vendor/autoload.php');
use \Brightzone\GremlinDriver\Connection;

error_reporting(E_ALL ^ E_WARNING);

// Write your own configuration values here
$db = new Connection([
    'host' => 'your_server_address',
    'username' => '/dbs/your_database/colls/your_collection_or_graph',
    'password' => 'your_primary-key'
    ,'port' => '443'

    // Required parameter
    ,'ssl' => TRUE
]);

$db->timeout = 0.5; 

function dropGraph($db)
{
    $query = "g.V().drop()";
    printf("\t%s\n\tQuery: %s\n", "Dropping entire graph.", $query);
    $result = $db->send($query);
    $errors = array_filter($result);

    if($errors)
    {
        printf("\tSomething went wrong with this query:\n%s\n",$query);
        die();
    }

    printf("\tSuccesfully dropped the graph\n\n");
}

$_queries_insert_vertices = array(
    "Adding Thomas" => "g.addV('person').property('id', 'thomas').property('firstName', 'Thomas').property('age', 44).property('userid', 1)",
    "Adding Mary" => "g.addV('person').property('id', 'mary').property('firstName', 'Mary').property('lastName', 'Andersen').property('age', 39).property('userid', 2)"
);

function addVertices($db, $_queries_insert_vertices)
{
    $i = 0;
    foreach ($_queries_insert_vertices as $key => $value)
    {
        printf("\t%s\n\tQuery: %s\n", $key, $value);
        $result = $db->send($value);
        $i++;
        if(!$result)
        {
            printf("\tSomething went wrong with this query:\n%s\n",$value);
            $i--;
        }
    }
    printf("\tSuccessfully inserted: %d vertices\n\n",$i);
}

$_queries_insert_edges = array(
    "Thomas knows Mary" => "g.V('thomas').addE('knows').to(g.V('mary'))",
    "Mary employs Thomas" => "g.V('mary').addE('employs').to(g.V('thomas'))"
);

function addEdges($db, $_queries_insert_edges)
{
    $i = 0;
    foreach ($_queries_insert_edges as $key => $value)
    {
        printf("\t%s\n\tQuery: %s\n", $key, $value);
        $result = $db->send($value);
        $i++;
        if(!$result)
        {
            printf("\tSomething went wrong with this query:\n%s\n",$value);
            $i--;
        }
    }
    printf("\tSuccessfully inserted: %d edges\n\n",$i);
}

function countVertices($db)
{
    $query = "g.V().count()";
    printf("\t%s\n\tQuery: %s\n", "Counting all the vertices.", $query);
    $result = $db->send($query);

    if($result)
    {
        printf("\tNumber of vertices in this graph: %s\n\n", $result[0]);
    }
}

function pressAnyKeyToContinuePrompt($message)
{
    printf("%s. Press any key to continue\n", $message);
    $fp = fopen("php://stdin","r");
    fgets($fp);
}

try {

    print "Welcome to Azure Cosmos DB + Gremlin on PHP!\n\n";
    print "Attempting to connect...\n";
    $db->open();
    print "Successfully connected to the database\n\n";

    pressAnyKeyToContinuePrompt("We will proceed to drop whatever graph is on your collection.");
    dropGraph($db);

    pressAnyKeyToContinuePrompt("Great! Now that we have a fresh collection we can proceed to insert a few vertices.");
    addVertices($db, $_queries_insert_vertices);

    pressAnyKeyToContinuePrompt("Now that we have the vertices, let's add a relationship between them.");
    addEdges($db, $_queries_insert_edges);
    
    pressAnyKeyToContinuePrompt("Cool! Now let's run some aggregates.");
    countVertices($db);
    
    pressAnyKeyToContinuePrompt("And that's our demo!");

    $db->close();
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>
