<?php
/**
 * Personnage
 *
 * PHP Version 7.1
 *
 * @category Categorie
 * @package  Package
 * @author   Hash Carter <coulang43@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/momise92/MiniJeuxDeCombat
 */
class Personnage
{
    private $_id,
    $_degats,
    $_nom;

    const CEST_MOI = 1; // Constante renvoyée par la méthode `frapper` si on se frappe soi-même.
    const PERSONNAGE_TUE = 2; // Constante renvoyée par la méthode `frapper` si on a tué le personnage en le frappant.
    const PERSONNAGE_FRAPPE = 3; // Constante renvoyée par la méthode `frapper` si on a bien frappé le personnage.

    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    public function frapper(Personnage $perso)
    {
        // Avant tout : vérifier qu'on ne se frappe pas soi-même.
        // Si c'est le cas, on stoppe tout en renvoyant une valeur signifiant que le personnage ciblé est le personnage qui attaque.
        // On indique au personnage frappé qu'il doit recevoir des dégâts.
    }

    public function recevoirDegats()
    {
        // On augmente de 5 les dégâts.
        
        // Si on a 100 de dégâts ou plus, la méthode renverra une valeur signifiant que le personnage a été tué.
        
        // Sinon, elle renverra une valeur signifiant que le personnage a bien été frappé.
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = 'set'.ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function degats()
    {
        return $this->_degats;
    }

    public function id()
    {
        return $this->_id;
    }

    public function nom()
    {
        return $this->_nom;
    }

    public function setDegats($degats)
    {
        $degats = (int) $degats;
    
        if ($degats >= 0 && $degats <= 100) {
            $this->_degats = $degats;
        }
    }

    public function setId($id)
    {
        $id = (int) $id;
    
        if ($id > 0) {
            $this->_id = $id;
        }
    }

    public function setNom($nom)
    {
        if (is_string($nom)) {
            $this->_nom = $nom;
        }
    }
}
