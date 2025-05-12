
// class User
// {
//     private $user_id = null;
//     public function __construct($user_id = null)
//     {
//         $this->user_id = $user_id;

//     }
//     public function get()
//     {
//         $conn = Conexao::getConnection();

//         $query = $conn->prepare("SELECT id, username, balance from usuarios 
//         where id = :id");
//         $query->bindValue(':id', $this->user_id);
//         if ($query->execute()) {
//             $row = $query->fetchAll(PDO::FETCH_OBJ);
//             if (count($row) > 0) {
//                 return $row[0];
//             } else {
//                 return false;
//             }
//         } else {
//             return false;
//         }


//     }
}