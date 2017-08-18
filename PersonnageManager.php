<?php
class PersonnageManager
{
    private $_db; // Instance de PDO

    public function __construct($db)
    {
        $this->setDb($db);
    }

    public function add(Personnage $perso)
    {
        $q = $this->_db->prepare('INSERT INTO personnages(nom) VALUES (:nom)');
        $q->bindValue(':nom', $perso->nom());
        $q->execute();

        $perso->hydrate([
            'id' => $this->_db->lastInsertId(),
            'degats' => 0,
        ]);
    }

    public function count()
    {
        // Exécute une requête COUNT() et retourne le nombre de résultats retourné.
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }

    public function delete(Personnage $perso)
    {
        // Exécute une requête de type DELETE.
        $this->_db->exec('DELETE FROM personnages WHERE id = '.$perso->id());
    }

    public function exists($info)
    {
        // Si le paramètre est un entier, c'est qu'on a fourni un identifiant.
        // On exécute alors une requête COUNT() avec une clause WHERE, et on retourne un boolean.
        if (is_int($info)) { // On veut voir si tel personnage ayant pour id $info existe.
            return (bool) $this->_db->query('SELECT COUNT(*) FROM personnages WHERE id = '.$info)->fetchColumn();
        }
    
        // Sinon c'est qu'on a passé un nom.
        // Exécution d'une requête COUNT() avec une clause WHERE, et retourne un boolean.
        $q = $this->_db->prepare('SELECT COUNT(*) FROM personnages WHERE nom = :nom');
        $q->execute([':nom' => $info]);
    
        return (bool) $q->fetchColumn();
    }
}

public function get($info)
{
    // Si le paramètre est un entier, on veut récupérer le personnage avec son identifiant.
    // Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
    if (is_int($info)) {
        $q = $this->_db->query('SELECT id, nom, degats FROM personnages WHERE id = '.$info);
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
    
        return new Personnage($donnees);
    } // Sinon, on veut récupérer le personnage avec son nom.
    /* Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage. */
    else {
        $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom = :nom');
        $q->execute([':nom' => $info]);
    
        return new Personnage($q->fetch(PDO::FETCH_ASSOC));
    }
}

public function getList($nom)
{
    /* Retourne la liste des personnages dont le nom n'est pas $nom.
    Le résultat sera un tableau d'instances de Personnage.*/
    $persos = [];
    
    $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom <> :nom ORDER BY nom');
    $q->execute([':nom' => $nom]);
    
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
        $persos[] = new Personnage($donnees);
    }
    
    return $persos;
}

public function update(Personnage $perso)
{
    /* Prépare une requête de type UPDATE.
    Assignation des valeurs à la requête.
    Exécution de la requête. */
    $q = $this->_db->prepare('UPDATE personnages SET degats = :degats WHERE id = :id');
    
    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
    $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
    
    $q->execute();
}

public function setDb(PDO $db)
{
    $this->_db = $db;
}
}


// On enregistre notre autoload.
function chargerClasse($classname)
{
    require $classname.'.php';
}

spl_autoload_register('chargerClasse');

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: .');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=combats', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // On émet une alerte à chaque fois qu'une requête a échoué.

$manager = new PersonnagesManager($db);

if (isset($_SESSION['perso'])) { // Si la session perso existe, on restaure l'objet.
    $perso = $_SESSION['perso'];
}

if (isset($_POST['creer']) && isset($_POST['nom'])) { // Si on a voulu créer un personnage.
    $perso = new Personnage(['nom' => $_POST['nom']]); // On crée un nouveau personnage.
  
    if (!$perso->nomValide()) {
        $message = 'Le nom choisi est invalide.';
        unset($perso);
    } elseif ($manager->exists($perso->nom())) {
        $message = 'Le nom du personnage est déjà pris.';
        unset($perso);
    } else {
        $manager->add($perso);
    }
} elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) { // Si on a voulu utiliser un personnage.
    if ($manager->exists($_POST['nom'])) { // Si celui-ci existe.
        $perso = $manager->get($_POST['nom']);
    } else {
        $message = 'Ce personnage n\'existe pas !'; // S'il n'existe pas, on affichera ce message.
    }
} elseif (isset($_GET['frapper'])) { // Si on a cliqué sur un personnage pour le frapper.
    if (!isset($perso)) {
        $message = 'Merci de créer un personnage ou de vous identifier.';
    } else {
        if (!$manager->exists((int) $_GET['frapper'])) {
            $message = 'Le personnage que vous voulez frapper n\'existe pas !';
        } else {
            $persoAFrapper = $manager->get((int) $_GET['frapper']);
      
            $retour = $perso->frapper($persoAFrapper); // On stocke dans $retour les éventuelles erreurs ou messages que renvoie la méthode frapper.
      
            switch ($retour) {
                case Personnage::CEST_MOI:
                    $message = 'Mais... pourquoi voulez-vous vous frapper ???';
                    break;
        
                case Personnage::PERSONNAGE_FRAPPE:
                    $message = 'Le personnage a bien été frappé !';
          
                    $manager->update($perso);
                    $manager->update($persoAFrapper);
          
                    break;
        
                case Personnage::PERSONNAGE_TUE:
                    $message = 'Vous avez tué ce personnage !';
          
                    $manager->update($perso);
                    $manager->delete($persoAFrapper);
          
                    break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>TP : Mini jeu de combat</title>
    
    <meta charset="utf-8" />
  </head>
  <body>
    <p>Nombre de personnages créés : <?= $manager->count() ?></p>
<?php
if (isset($message)) { // On a un message à afficher ?
    echo '<p>', $message, '</p>'; // Si oui, on l'affiche.
}

if (isset($perso)) { // Si on utilise un personnage (nouveau ou pas).
?>
    <p><a href="?deconnexion=1">Déconnexion</a></p>
    
    <fieldset>
      <legend>Mes informations</legend>
      <p>
        Nom : <?= htmlspecialchars($perso->nom()) ?><br />
        Dégâts : <?= $perso->degats() ?>
      </p>
    </fieldset>
    
    <fieldset>
      <legend>Qui frapper ?</legend>
      <p>
<?php
$persos = $manager->getList($perso->nom());

if (empty($persos)) {
    echo 'Personne à frapper !';
} else {
    foreach ($persos as $unPerso) {
        echo '<a href="?frapper=', $unPerso->id(), '">', htmlspecialchars($unPerso->nom()), '</a> (dégâts : ', $unPerso->degats(), ')<br />';
    }
}
?>
      </p>
    </fieldset>
<?php
} else {
?>
    <form action="" method="post">
      <p>
        Nom : <input type="text" name="nom" maxlength="50" />
        <input type="submit" value="Créer ce personnage" name="creer" />
        <input type="submit" value="Utiliser ce personnage" name="utiliser" />
      </p>
    </form>
<?php
}
?>
  </body>
</html>
<?php
if (isset($perso)) { // Si on a créé un personnage, on le stocke dans une variable session afin d'économiser une requête SQL.
    $_SESSION['perso'] = $perso;
}