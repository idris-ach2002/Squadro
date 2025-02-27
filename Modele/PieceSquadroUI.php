<?php
require_once 'plateau_squadro.php';

/**
 * Classe représentant l'interface utilisateur pour le jeu Squadro.
 * Cette classe gère l'affichage et les interactions avec le plateau et les pièces.
 */
class PieceSquadroUI
{

    /**
     * @var PlateauSquadro Plateau de jeu utilisé pour gérer les états des cases et des pièces.
     */
    private PlateauSquadro $plate;
    private static array $zone;

    /**
     * Constructeur privé pour initialiser l'état par défaut de la classe.
     */

    private function __construct(PlateauSquadro $p)
    {
        $this->plate = $p;
    }


    public static function getPlateau(): PlateauSquadro
    {
        return self::$plate;
    }


    /**
     * Génère un bouton représentant une case d'avancement.
     *
     * @param int $avancer Valeur d'avancement affichée.
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML du bouton.
     */
    private static function genererCaseAvancement(int $avancer, string $id): string
    {
        return "<input type='button' class='red' value='$avancer' id='$id'/>";
    }
    /**
     * Génère une case rouge.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case rouge.
     */
    private static function genererCaseRouge(string $id): string
    {
        return "<input type='button' class='red' id='$id'/>";
    }

    /**
     * Génère une case blanche.
     *
     * @param int $x Coordonnée x de la case.
     * @param int $y Coordonnée y de la case.
     * @param int $direction Direction de la pièce (1 pour BE, 0 pour BW).
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case blanche.
     */
    private static function genererCaseBlanche(int $x, int $y, int $direction, string $id, string $disable): string
    {
        $position = $direction == 1 ? "BE" : "BW";
        $style = $direction == 1 ? "border-right: solid #c9c6ac 10px;" : " border-left: solid #c9c6ac 10px;";
        return "<button type='submit' style='$style' class='white' name='blanc' value='$id' id='$id' $disable>$position</button>";
    }

    /**
     * Génère une case noire.
     *
     * @param int $x Coordonnée x de la case.
     * @param int $y Coordonnée y de la case.
     * @param int $direction Direction de la pièce (1 pour NS, 0 pour NN).
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case noire.
     */

    private static function genererCaseNoir(int $x, int $y, int $direction, string $id, string $disable): string
    {
        $position = $direction == 0 ? "NN" : "NS";
        $style = $direction == 0 ? " border-top: solid black 10px;" : "border-bottom: solid black 10px;";
        return "<button type='submit' class='black' style='$style' name='noir' value='$id' id='$id' $disable>$position</button>";
    }
    /**
     * Génère une case vide.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case vide.
     */
    private static function genererCaseVide(string $id): string
    {
        return "<input type='button' class='vide' disabled id='$id'/>";
    }


    /**
     * Génère une case neutre.
     *
     * @param string $id ID HTML de l'élément.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML de la case neutre.
     */
    private static function genererCaseNeutre(string $id): string
    {
        return "<input type='button' class='neutre' disabled id='$id'/>";
    }


    /**
     * Démarre un formulaire HTML.
     *
     * @param string $fich Action du formulaire.
     * @return string Code HTML du début du formulaire.
     */
    public static function debForm(string $fich): string
    {
        return "<form action='$fich' method='post' class='app'>";
    }

    /**
     * Termine un formulaire HTML.
     *
     * @return string Code HTML de la fin du formulaire.
     */
    public static function finForm(): string
    {
        return "</form>";
    }

    private static function styleApp(): string
    {
        return '
                body {
                    background-image: url("Logo/valhalla.jpg");
                    background-position: cover;
                    background-repeat: repeat;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }

                .confirmation {
                    display: flex;
                    margin: auto;
                }

                .app {
                    display: grid;
                    width: 1000px;
                    height: 1000px;
                    margin:  auto;
                    grid-template-columns: repeat(9,100px);
                    grid-template-rows: repeat(9,100px);

                    grid-template-areas: 
                    "zone1  zone2  zone3  zone4  zone5  zone6  zone7   zone8  zone9"

                    "zone10   zone11 zone12 zone13 zone14 zone15 zone16 zone17   zone18"
                    "zone19   zone20 zone21 zone22 zone23 zone24 zone25 zone26   zone27"
                    "zone28   zone29 zone30 zone31 zone32 zone33 zone34 zone35   zone36"
                    "zone37   zone38 zone39 zone40 zone41 zone42 zone43 zone44   zone45"
                    "zone46   zone47 zone48 zone49 zone50 zone51 zone52 zone53   zone54"
                    "zone55   zone56 zone57 zone58 zone59 zone60 zone61 zone62   zone63"
                    "zone64   zone65 zone66 zone67 zone68 zone69 zone70 zone71   zone72"

                    "zone73 zone74 zone75 zone76 zone77 zone78 zone79 zone80 zone81";
                }


                #btn1 , #btn9, #btn17, #btn25{
                    width: 80%;
                    height: 80%; 
                    margin: auto;
                    background-color: red;
                    box-shadow:     inset 0 -3em 3em rgb(0 200 0 / 30%),
                                    0 0 0 2px white,
                                    0.3em 0.3em 1em rgb(200 0 0 / 60%);
                }';
    }

    private static function zoneButtonStatic()
    {
        return '
      
                #btn1 { grid-area: zone1; }
                #btn2 { grid-area: zone2; }
                #btn3 { grid-area: zone3; }
                #btn4 { grid-area: zone4; }
                #btn5 { grid-area: zone5; }
                #btn6 { grid-area: zone6; }
                #btn7 { grid-area: zone7; }
                #btn8 { grid-area: zone8; }
                #btn9 { grid-area: zone9; }

                #btn10 { grid-area: zone18; }
                #btn11 { grid-area: zone27; }
                #btn12 { grid-area: zone36; }
                #btn13 { grid-area: zone45; }
                #btn14 { grid-area: zone54; }
                #btn15 { grid-area: zone63; }
                #btn16 { grid-area: zone72; }

                #btn17 { grid-area: zone81; }
                #btn18 { grid-area: zone80; }
                #btn19 { grid-area: zone79; }
                #btn20 { grid-area: zone78; }
                #btn21 { grid-area: zone77; }
                #btn22 { grid-area: zone76; }
                #btn23 { grid-area: zone75; }
                #btn24 { grid-area: zone74; }
                #btn25 { grid-area: zone73; }

                #btn26 { grid-area: zone64; }
                #btn27 { grid-area: zone55; }
                #btn28 { grid-area: zone46; }
                #btn29 { grid-area: zone37; }
                #btn30 { grid-area: zone28; }
                #btn31 { grid-area: zone19; }
                #btn32 { grid-area: zone10; }
        ';
    }


    private static function effetExplosion(): string
    {
        return '
           #btn1, #btn9, #btn17, #btn25 {
            position: relative;
            animation: rotationInfini 2s linear infinite, 
                    flamme 0.4s infinite alternate ease-in-out, 
                    electric 0.1s infinite alternate-reverse, 
                    distort 0.7s infinite alternate ease-in-out;
            box-shadow: 0 0 40px #ff4500, 0 0 80px #ff0000, 0 0 120px rgba(255, 69, 0, 1);
            transition: transform 0.15s ease-in-out, filter 0.15s;
            background-image: url("Logo/dragon1.jpg");
            background-size: 100px 100px; /* Définit une largeur et hauteur spécifiques */
            background-position: center;
            background-repeat: no-repeat;
            border: 10px dashed rgba(255, 255, 255, 0.89);
            color: white;
            font-weight: bold;
            text-shadow: 0 0 10px yellow, 0 0 20px orange;
        }


        @keyframes rotationInfini {
            0% { transform: rotate(0deg) scale(1); }
            100% { transform: rotate(360deg) scale(1.1); }
        }

        @keyframes distort {
            0% { transform: scale(1.05) rotate(-2deg); }
            100% { transform: scale(1.1) rotate(2deg); }
        }

       
        @keyframes flamme {
            0% { filter: brightness(1.5) hue-rotate(10deg); }
            100% { filter: brightness(2) hue-rotate(-10deg); }
        }


        @keyframes electric {
            0% { box-shadow: 0 0 50px #ff4500, 0 0 120px rgba(255, 69, 0, 1); }
            100% { box-shadow: 0 0 80px #ff4500, 0 0 160px rgba(255, 69, 0, 1); }
        }


        #btn1:hover, #btn9:hover, #btn17:hover, #btn25:hover {
            animation: explode 0.5s ease-in-out forwards;
        }

        @keyframes explode {
            0% { transform: scale(1.5) rotate(10deg); filter: brightness(3); opacity: 1; }
            100% { transform: scale(5) rotate(0deg); opacity: 0; }
        }

        #btn1::before, #btn9::before, #btn17::before, #btn25::before {
            content: "";
            position: absolute;
            width: 120%;
            height: 120%;
            top: -10%;
            left: -10%;
            background: radial-gradient(circle, rgba(255, 69, 0, 0.6) 10%, transparent 70%);
            opacity: 0.8;
            filter: blur(20px);
            animation: particles 0.3s infinite alternate;
        }

        @keyframes particles {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.3); opacity: 0.5; }
        }


        ';
    }


    private static function effetSanglant(): string
    {
        return '
                
                    #btn2:hover,#btn3:hover,#btn4:hover,#btn5:hover,#btn6:hover,
                    #btn7:hover,#btn8:hover,#btn10:hover,#btn11:hover,#btn12:hover,
                    #btn13:hover,#btn14:hover,#btn15:hover,#btn16:hover,#btn18:hover,
                    #btn19:hover,#btn20:hover,#btn21:hover,#btn22:hover,#btn23:hover,
                    #btn24:hover,#btn26:hover,#btn27:hover,#btn28:hover,#btn29:hover,
                    #btn30:hover,#btn31:hover,#btn32:hover {
                        border: none;
                        font-size: 3rem;
                        background: linear-gradient(90deg, #ff0000, #800000, #ff0000);
                        box-shadow: 0 0 20px #ff0000, 
                                    0 0 50px #ff0000, 
                                    0 0 80px rgba(255, 0, 0, 0.8);
                        transform: scale(0.9) rotate(2deg);
                        animation: portail 1s infinite linear;
                        }

                        @keyframes portail {
                            0% { background-position: 0% 50%; }
                            100% { background-position: 100% 50%; }
                        }

        ';
    }

    private static function styleCaseNeutre(): string
    {
        return '
              #btn0-0,#btn6-0,#btn0-6,#btn6-6 {
                cursor: not-allowed;
                background-image: url("Logo/samurai3.jpeg");
                background-size: 100px 100px; /* Définit une largeur et hauteur spécifiques */
                background-position: center;
                background-repeat: no-repeat;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
                transition: all 0.3s ease-in-out;
            }


            @keyframes cosmicWave {
                0% { transform: scale(1) rotate(0deg); }
                25% { transform: scale(0.8) rotate(10deg); }
                50% { transform: scale(0.85) rotate(-10deg); }
                75% { transform: scale(0.95) rotate(5deg); }
                100% { transform: scale(1) rotate(0deg); }
            }

            #btn0-0:hover,#btn6-0:hover,#btn0-6:hover,#btn6-6:hover {
                background-image: none;
                border: 15px ridge rgb(249, 193, 140);
                animation: cosmicWave 1.5s infinite;
                box-shadow: 0 0 100px rgba(255, 255, 255, 0.8), 0 0 150px rgba(0, 0, 0, 0.8);
            }


            @keyframes particleBurst {
                0% { opacity: 0; transform: scale(0.5); }
                50% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(0.5); }
            }

            #btn0-0::before,#btn6-0::before,#btn0-6::before,#btn6-6::before {
                content: "";
                position: absolute;
                top: -30px;
                left: -30px;
                width: 100%;
                height: 100%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.5) 30%, transparent 70%);
                border-radius: 50%;
                animation: particleBurst 0.6s infinite ease-in-out;
                opacity: 0;
                transition: opacity 0.4s ease;
            }

            #btn0-0:hover::before,#btn6-0:hover::before,#btn0-6:hover::before,#btn6-6:hover::before {
                opacity: 1;
            }

            @keyframes nebula {
                0% { background: linear-gradient(135deg, rgb(245, 245, 245), rgb(0, 0, 0)); }
                50% { background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgb(255, 255, 255)); }
                100% { background: linear-gradient(135deg, rgb(255, 255, 255), rgb(255, 255, 255)); }
            }

            #btn0-0:hover,#btn6-0:hover,#btn0-6:hover,#btn6-6:hover {
                animation: nebula 0.6s infinite ease-in-out;
                box-shadow: 0 0 200px rgb(0, 0, 0), 0 0 200px rgb(255, 255, 255);
            }

            #btn0-0::after,#btn6-0::after,#btn0-6::after,#btn6-6::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 200%;
                height: 200%;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                filter: blur(30px);
                transform: translate(-50%, -50%) scale(0);
                animation: particleBurst 0.8s infinite ease-in-out;
                opacity: 0;
                transition: opacity 0.4s ease;
            }

            #btn0-0:hover::after,#btn6-0:hover::after,#btn0-6:hover::after,#btn6-6:hover::after {
                opacity: 0.7;
                transform: translate(-50%, -50%) scale(1);
            }
        ';
    }


    private static function styleVictoire(): string
    {
        return "
                    /* Style pour le conteneur de la victoire */
                        .victoire {
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            background-color: #000;
                            overflow: hidden;
                            position: relative;
                        }

                        /* Style pour le texte du gagnant */
                        .winner {
                            font-family: 'Arial Black', sans-serif;
                            font-size: 5rem;
                            color: #fff;
                            text-transform: uppercase;
                            letter-spacing: 0.1em;
                            text-align: center;
                            margin: 0;
                            z-index: 1;
                            animation: textGlow 1.5s ease-in-out infinite alternate, textZoom 3s ease-in-out infinite;
                        }

                        /* Animation de lueur du texte */
                        @keyframes textGlow {
                            from {
                                text-shadow: 0 0 10px #fff, 0 0 20px #ff00ff, 0 0 30px #ff00ff, 0 0 40px #ff00ff, 0 0 50px #ff00ff, 0 0 60px #ff00ff, 0 0 70px #ff00ff;
                            }
                            to {
                                text-shadow: 0 0 20px #fff, 0 0 30px #ff00ff, 0 0 40px #ff00ff, 0 0 50px #ff00ff, 0 0 60px #ff00ff, 0 0 70px #ff00ff, 0 0 80px #ff00ff;
                            }
                        }

                        /* Animation de zoom du texte */
                        @keyframes textZoom {
                            0%, 100% {
                                transform: scale(1);
                            }
                            50% {
                                transform: scale(1.1);
                            }
                        }

                        /* Conteneur des feux d'artifice */
                        .fireworks {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            pointer-events: none;
                            z-index: 0;
                        }

                        /* Style pour chaque feu d'artifice */
                        .firework {
                            position: absolute;
                            bottom: 0;
                            width: 5px;
                            height: 5px;
                            background-color: transparent;
                            box-shadow: 0 0 5px 2px #fff;
                            border-radius: 50%;
                            animation: launch 2s ease-in-out infinite, explodeWin 1s ease-in-out infinite;
                            animation-delay: calc(var(--i) * 0.5s);
                        }

                        /* Animation de lancement */
                        @keyframes launch {
                            0% {
                                transform: translateY(0);
                                opacity: 1;
                            }
                            80% {
                                opacity: 1;
                            }
                            100% {
                                transform: translateY(-500px);
                                opacity: 0;
                            }
                        }

                        /* Animation d'explosion */
                        @keyframes explodeWin {
                            0%, 80% {
                                opacity: 0;
                                transform: scale(0);
                            }
                            81% {
                                opacity: 1;
                                transform: scale(1);
                            }
                            100% {
                                opacity: 0;
                                transform: scale(1.5);
                            }
                        }

        ";
    }

    private static function styleBoiteConfirmation(): string
    {
        return "
                      /* Styles pour la boîte de confirmation */
                .confirmation {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                    text-align: center;
                    opacity: 0;
                    animation: fadeInScale 0.5s forwards;
                }

                /* Animation d apparition */
                @keyframes fadeInScale {
                    0% {
                        opacity: 0;
                        transform: translate(-50%, -50%) scale(0.8);
                    }
                    100% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1);
                    }
                }

                /* Styles pour les boutons */
                .btn {
                    background-color: #4CAF50;
                    border: none;
                    color: white;
                    padding: 10px 24px;
                    margin: 10px;
                    border-radius: 5px;
                    cursor: pointer;
                    transition: background-color 0.3s, transform 0.3s;
                }

                /* Effet au survol des boutons */
                .btn:hover {
                    background-color: #45a049;
                    transform: translateY(-2px);
                }

                /* Effet lors du clic sur les boutons */
                .btn:active {
                    transform: translateY(2px);
                }

            ";
    }

    private static function styleButton(): string
    {
        return '
                input[type="button"], button[type="submit"] {
                    width: 100%;
                    height: 100%;
                    border: 1px solid #ccc;
                    font-size: 14px;
                    font-weight: bold;
                    text-align: center;
                    cursor: pointer;
                }

                input[type="submit"], button[type="submit"] {
                    border: solid #000000 1px;
                }

                input[type="button"]:disabled, button:disabled {
                    cursor: not-allowed;
                }

                input.red {
                    cursor: not-allowed;
                    background-color:rgba(0, 0, 0, 0.83);
                    font-size: 2rem;
                    font-weight: 700;
                    color: white;
                    transform: scale(0.98);
                    border-radius: 15px;
                    border: 20px ridge rgba(255, 255, 255, 0.89);
                }
                    

                button.white {
                    background-image: url("Logo/femmeGrec.png");
                    background-size: 100px 100px; /* Définit une largeur et hauteur spécifiques */
                    background-position: center;
                    background-repeat: no-repeat;
                    color: white;
                }

                button.black {
                    background-image: url("Logo/guerrier.jpg");
                    background-size: 100px 100px; /* Définit une largeur et hauteur spécifiques */
                    background-position: center;
                    background-repeat: no-repeat;
                    color: white;
                }

                input.vide {
                    background-color: #a9a9a9;
                }

        '  .    self::effetExplosion() . self::effetSanglant() . 
                self::styleCaseNeutre() . self::styleVictoire() . 
                self::styleBoiteConfirmation();
    }

    private static function attribuerZone()
    {
        if (!isset(self::$zone))
            self::$zone = array_fill(0, 7, array_fill(0, 7, null));

        self::$zone[0][0] = "zone11";
        self::$zone[0][1] = "zone12";
        self::$zone[0][2] = "zone13";
        self::$zone[0][3] = "zone14";
        self::$zone[0][4] = "zone15";
        self::$zone[0][5] = "zone16";
        self::$zone[0][6] = "zone17";
        self::$zone[1][0] = "zone20";
        self::$zone[1][1] = "zone21";
        self::$zone[1][2] = "zone22";
        self::$zone[1][3] = "zone23";
        self::$zone[1][4] = "zone24";
        self::$zone[1][5] = "zone25";
        self::$zone[1][6] = "zone26";
        self::$zone[2][0] = "zone29";
        self::$zone[2][1] = "zone30";
        self::$zone[2][2] = "zone31";
        self::$zone[2][3] = "zone32";
        self::$zone[2][4] = "zone33";
        self::$zone[2][5] = "zone34";
        self::$zone[2][6] = "zone35";
        self::$zone[3][0] = "zone38";
        self::$zone[3][1] = "zone39";
        self::$zone[3][2] = "zone40";
        self::$zone[3][3] = "zone41";
        self::$zone[3][4] = "zone42";
        self::$zone[3][5] = "zone43";
        self::$zone[3][6] = "zone44";
        self::$zone[4][0] = "zone47";
        self::$zone[4][1] = "zone48";
        self::$zone[4][2] = "zone49";
        self::$zone[4][3] = "zone50";
        self::$zone[4][4] = "zone51";
        self::$zone[4][5] = "zone52";
        self::$zone[4][6] = "zone53";
        self::$zone[5][0] = "zone56";
        self::$zone[5][1] = "zone57";
        self::$zone[5][2] = "zone58";
        self::$zone[5][3] = "zone59";
        self::$zone[5][4] = "zone60";
        self::$zone[5][5] = "zone61";
        self::$zone[5][6] = "zone62";
        self::$zone[6][0] = "zone65";
        self::$zone[6][1] = "zone66";
        self::$zone[6][2] = "zone67";
        self::$zone[6][3] = "zone68";
        self::$zone[6][4] = "zone69";
        self::$zone[6][5] = "zone70";
        self::$zone[6][6] = "zone71";
    }

    private static function zoneDynamique(): string
    {
        self::attribuerZone();
        $res = '';
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                $res .=  "#btn$i-$j { grid-area: " . self::$zone[$i][$j] . "; }<br/>";
            }
        }
        return $res;
    }


    public static function createStyle(): string
    {
        $res = '';
        $res .= self::styleApp();
        $res .= self::zoneButtonStatic();
        $res .= self::zoneDynamique();
        $res .= self::styleButton();
        return $res;
    }

    /**
     * Génère des cases rouges avec des valeurs d'avancement.
     *
     * @param array $vitesse Tableau des valeurs d'avancement.
     * @param int $id ID de départ pour les cases.
     * @param int $nbCases Nombre de cases rouges avant/après les valeurs d'avancement.
     * @param string $disable Statut de désactivation.
     * @return string Code HTML des cases rouges.
     */
    private static function casesRouges(array $vitesse, int $id, int $nbCases): string
    {
        $res = "";
        $compteur = $id;
        for ($i = 0; $i < $nbCases; $i++) {
            $res .= self::genererCaseRouge("btn$compteur") . "\n";
            $compteur++;
        }

        foreach ($vitesse as $v) {
            $res .= self::genererCaseAvancement($v, "btn$compteur") . "\n";
            $compteur++;
        }

        for ($i = 0; $i < $nbCases; $i++) {
            $res .= self::genererCaseRouge("btn$compteur") . "\n";
            $compteur++;
        }

        return $res;
    }

    /**
     * Génère l'interface utilisateur complète du plateau.
     *
     * @param string $fich Action du formulaire.
     * @return string Code HTML complet de l'interface du plateau.
     */

    public static function plateauUI(PlateauSquadro $p, string $noir, string $blanc): string
    {

        $res = "";



        // Génération des cases rouges
        $res .= self::casesRouges([3, 1, 2, 1, 3], 10, 1) . "\n";
        $res .= self::casesRouges([1, 3, 2, 3, 1], 1, 2) . "\n";
        $res .= self::casesRouges([3, 1, 2, 1, 3], 17, 2) . "\n";
        $res .= self::casesRouges([1, 3, 2, 3, 1], 26, 1) . "\n";

        // Génération des cases du plateau
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                switch ($p->getPiece($i, $j)->getCouleur()) {
                    case -2: //case neutre
                        $res .= self::genererCaseNeutre("btn$i-$j") . "\n";
                        break;
                    case -1: // case vide 
                        $res .= self::genererCaseVide("btn$i-$j") . "\n";
                        break;
                    case 0: // case blanche
                        $res .= self::genererCaseBlanche($i, $j, $p->getPiece($i, $j)->getDirection(), "btn$i-$j", $blanc) . "\n";
                        break;
                    case 1: // case noir
                        $res .= self::genererCaseNoir($i, $j, $p->getPiece($i, $j)->getDirection(), "btn$i-$j", $noir) . "\n";
                        break;
                }
            }
        }
        return $res;
    }

    /**
     * Affiche une boîte de confirmation pour un déplacement de pièce.
     *
     * @param string $fich Action du formulaire.
     * @param string $pieceId Identifiant de la pièce.
     * @return string Code HTML de la confirmation.
     */
    public static function confirmationDeplacement(string $fich, string $pieceId,  PlateauSquadro $p): string
    {
        $res = "";
        $res .= self::debForm($fich);
        $res .= self::plateauUI($p, "disabled", "disabled");
        $res .= "<div class='confirmation'";
        $res .= "<p>Voulez-vous déplacer la pièce $pieceId ?</p>";
        $res .= "<input type='submit' value='PRESEED' name='choix' class='btn'/><br/>";
        $res .= "<input type='submit' value='ABORT' name='choix' class='btn'/><br/>";
        $res .= "</div>";
        $res .= self::finForm();
        return $res;
    }
    /**
     * Affiche un message de victoire pour un joueur.
     *
     * @param string $joueur Nom du joueur gagnant.
     * @return string Code HTML du message de victoire.
     */
    public static function afficherVictoire(string $nomjoueur): string
    {
        return     "<div class='victoire'>
                        <h1 class='winner'>Le joueur $nomjoueur a gagné !</h1>
                        <div class='fireworks'>
                            <div class='firework' style='--i:1;'></div>
                            <div class='firework' style='--i:2;'></div>
                            <div class='firework' style='--i:3;'></div>
                            <div class='firework' style='--i:4;'></div>
                            <div class='firework' style='--i:5;'></div>
                        </div>
                    </div>";
    }


    public function afficher_erreur(string $erreur, string $fichier, PlateauSquadro $p, string $noir, string $blanc): string
    {
        return "<h1>Erreur : $erreur</h1><br/>" .
            self::debForm($fichier) .
            self::plateauUI($p, $noir, $blanc) .
            self::finForm();
    }
}

