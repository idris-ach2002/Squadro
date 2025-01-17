class PieceSquadro {
	// type de case 
	public const NOIR = 1;
	public const VIDE = -1;
	public const BLANC = 0;
	public const NEUTRE = -2;
	public const NORD = 0;
	public const EST = 1;
	public const SUD = 2;
	public const OUEST = 3;
	

	//variable d'instances
	protected int couleur;
	protected int direction;

    public function  __construct(int $couleur, it $direction) {
        $this->couleur = $couleur;
        $this->direction = direction;
    }

    public function getCouleur() : int {
        return this->couleur;
    }

    public function getDirection() : int {
        return this->direction;
    }



}