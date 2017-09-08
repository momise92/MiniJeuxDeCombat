<?php
abstract class Personnage
{
    protected $atout,
            $degats,
            $id,
            $nom,
            $timeEndormi,
            $type;
    
            const CEST_MOI = 1; // Constante renvoyée par la méthode `frapper` si on se frappe soit-même.
            const PERSONNAGE_TUE = 2; // Constante renvoyée par la méthode `frapper` si on a tué le personnage en le frappant.
            const PERSONNAGE_FRAPPE = 3; // Constante renvoyée par la méthode `frapper` si on a bien frappé le personnage.
            const PERSONNAGE_ENSORCELE = 4; // Constante renvoyée par la méthode `lancerUnSort` (voir classe Magicien) si on a bien ensorcelé un personnage.
            const PAS_DE_MAGIE = 5; // Constante renvoyée par la méthode `lancerUnSort` (voir classe Magicien) si on veut jeter un sort alors que la magie du magicien est à 0.
            const PERSO_ENDORMI = 6; // Constante renvoyée par la méthode `frapper` si le personnage qui veut frapper est endormi.

            public function __construct(array $donnees)
            {
                $this->hydrate($donnees);
                $this->type = strtolower(static::class);
            }

            public function estEndormi()
            {
                return $this->timeEndormi > time();
            }

            public function frapper(Personnage $perso)
            {
                if ($perso->id == $this->id) {
                    return self::CEST_MOI;
                }
                if ($this->estEndormi()) {
                    return self::PERSO_ENDORMI;
                }
                // On indique au personnage qu'il doit recevoir des dégâts.
                // Puis on retourne la valeur renvoyée par la méthode : self::PERSONNAGE_TUE ou self::PERSONNAGE_FRAPPE.
                return $perso->recevoirDegats();
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
            public function nomValide()
            {
                return !empty($this->nom);
            }
}
