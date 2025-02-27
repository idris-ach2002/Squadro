<?php

class ConceptionInterface
{

    public static function creerStylePageConexion() : string {
        return "
           /* Fond d'écran avec un effet flou */
            body {
                background: url('https://source.unsplash.com/1600x900/?technology,abstract') no-repeat center center/cover;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                font-family: 'Poppins', sans-serif;
            }

            /* Conteneur principal avec effet glassmorphism */
            .pageConexion {
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(15px);
                border-radius: 15px;
                padding: 40px;
                width: 400px;
                box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
                text-align: center;
                color: white;
                animation: fadeIn 1s ease-in-out;
            }

            /* Animation d'apparition fluide */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Titre stylé */
            .pageConexion h1 {
                font-size: 28px;
                color: black;
                margin-bottom: 20px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 2px;
            }

            /* Labels avec meilleur contraste */
            .pageConexion label {
                font-size: 16px;
                font-weight: bold;
                display: block;
                text-align: left;
                margin: 15px 0 5px;
                color: white; /* Texte blanc pour contraste */
            }

            /* Champs de saisie avec fond clair et texte foncé */
            .pageConexion input {
                width: 100%;
                padding: 12px;
                margin: 5px 0;
                border: 1px solid rgba(255, 255, 255, 0.5);
                border-radius: 5px;
                background: rgba(139, 139, 139, 0.06); /* Fond plus clair */
                color: #333; /* Texte foncé */
                font-size: 16px;
                outline: none;
                transition: 0.3s;
            }

            /* Placeholder plus visible */
            .pageConexion input::placeholder {
                color: rgba(0, 0, 0, 0.5);
            }

            /* Effet au focus */
            .pageConexion input:focus {
                border-color: #ff758c;
                background: white;
                box-shadow: 0 0 8px rgba(255, 117, 140, 0.8);
                transform: scale(1.02);
            }

            /* Bouton stylé */
            .pageConexion button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #ff7eb3, #ff758c);
                border: none;
                border-radius: 5px;
                color: white;
                font-size: 18px;
                font-weight: bold;
                text-transform: uppercase;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 20px;
            }

            .pageConexion button:hover {
                background: linear-gradient(135deg, #ff5277, #ff2e63);
                transform: scale(1.05);
            }



            /* Ajustement pour mobile */
            @media (max-width: 400px) {
                .pageConexion {
                    width: 90%;
                }
            }

        
        ";
    }
    
    public static function creerPageConexion(string $fich): string
    {
        $content = "<div class='pageConexion'>";
        $content .= "<h1>Authentification</h1>";
        $content .= "<form action='$fich' method='post'>";
        $content .= "<label for='login'>Login :</label>";
        $content .= "<input type='text' id='login' name='login' placeholder='Entrez votre login'/>";
        $content .= "<label for='pass'>Mot de passe :</label>";
        $content .= "<input type='password' id='pass' name='password' placeholder='********'/>";
        $content .= "<button type='submit'>Se connecter</button>";
        $content .= "</form>";
        $content .= "</div>";
        return $content;
    }
    
}
