<?php
//rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr

$sql="SHOW DATABASES";
$link = mysqli_connect('localhost', 'root', '') or die ('Error connecting to mysql: ' . mysqli_error($link).'\r\n');

if (!($result=mysqli_query($link,$sql))) {
        printf("Error: %s\n", mysqli_error($link));
    }
?>
<form method="GET" action="orm.php">
data base:<select name="database">
<?php
while( $row = mysqli_fetch_row( $result ) ){
        if (($row[0]!="information_schema") && ($row[0]!="mysql")) {
        	?>
        	<option value="<?php echo $row[0] ?>"><?php echo $row[0]?></option>
        	<?php
            //echo ;
        }
    }

//rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr
/*$link = mysql_connect('localhost', 'root', '');
$db_list = mysql_list_dbs($link);

?>

data base:<select name="database">
	<?php while ($row = mysql_fetch_object($db_list)) { ?>

	<option value="<?php echo $row->Database ?>"><?php echo $row->Database ?></option>

<?php
}
     //echo $row->Database . "\n";
     */
?>
</select>
<input type ="submit" value="generate" name="generate"> 
</form>
<?php
if(isset($_GET['generate'])) {
	$database=$_GET['database'];
	mkdir($database, 0777, true);
	mkdir($database."/include", 0777, true);
	mkdir($database."/controllers", 0777, true);
	mkdir($database."/models", 0777, true);
	mkdir($database."/views", 0777, true);
	
	$fic=fopen($database."/include/connexion.php", "w+");
	$contenu="<?php
	";
	$contenu.="\$db_name=\"".$database."\";
	";
	$contenu.="\$cnx= new PDO('mysql:host=localhost;dbname='.\$db_name, 'root', '');
	";
	$contenu.="\$cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	?>";

//ajouter cette instruction pour permettre l’affichage des messages d’erreurs
fwrite($fic,$contenu,100000000);
fclose($fic); 
//echo "<font color='green'>le controller ".$res[0]." est créé avec succes.</font><br>";




//echo $database;}
//else echo "string";
//if(isset(var))

include $database."/include/connexion.php";

echo "######################################################<br>
########### GENERATION DES CONTROLEURS ##########<br>
######################################################
<br>";

$req=$cnx->query("SHOW TABLES");
while($res=$req->fetch()){
//créer un dossier pour chaque ojet dans le dossiers views/
if(!file_exists($database."/views/".$res[0]))
	mkdir($database."/views/".$res[0], 0777, true);

$req1=$cnx->query("SELECT * FROM information_schema.columns WHERE table_name = '".$res[0]."' AND table_schema='".$db_name."'");
$res1=$req1->fetchAll();

//generer controller
$fic=fopen($database."/controllers/".$res[0].".controller.php", "w+");
$contenu="<?php
include \"models/".$res[0].".class.php\";
//initialisation des attributs de l’objet voiture
";
//print_r(EXEC sp_fkeys 'voiture') ;
//EXEC sp_fkeys 'voiture';

$resultt=$cnx->query("SELECT 
  TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_SCHEMA = '".$db_name."' AND
  REFERENCED_TABLE_NAME = 'voiture';");
print_r($resultt);

foreach($res1 as $val){
	
	//print_r($resultt);
	//print_r($val);
	//echo $val['COLUMN_KEY'];
	//echo $val['DATA_TYPE'];

$contenu.="$".$val['COLUMN_NAME']."='';
";
}
$contenu.="
//récuperation des valeurs des attributs de l’objet voiture
";
foreach($res1 as $val2){
$contenu.="if(isset(\$_REQUEST['".$val2['COLUMN_NAME']."'])) 
	\$".$val2['COLUMN_NAME']."=\$_REQUEST['".$val2['COLUMN_NAME']."'];
";
}

$contenu.="
//Instanciation de l’objet voiture
\$inst=new ".$res[0]."(";
$i=0;
foreach($res1 as $val){
$contenu.="$".$val['COLUMN_NAME'];
$i++;
if($i<count($res1))
$contenu.=",";
}
$contenu.=");
";
$contenu.="
switch(\$action){
case 'ajout1' : include 'views/".$res[0]."/ajout1.view.php';
break;

case 'ajout' : \$inst->ajout(\$cnx);
break;

case 'liste': \$res=\$inst->liste(\$cnx);
	include 'views/".$res[0]."/liste.view.php';
	break;
	
	case 'edit1': 
	\$res=\$inst->listWhereId(\$cnx);
	include 'views/".$res[0]."/edit.view.php';
	break;
	
	case 'edit': \$inst->edit(\$cnx);
	break;
	
	case 'delete': \$inst->delete(\$cnx);
	break;
	
}
?>";
fwrite($fic,$contenu,100000000);
fclose($fic); 
echo "<font color='green'>le controller ".$res[0]." est créé avec succes.</font><br>";
}



//generer les models
echo "<br>######################################################<br>
############# GENERATION DES MODELES #############<br>
######################################################
<br>";
$req=$cnx->query("SHOW TABLES");
while($res=$req->fetch()){
//créer un dossier pour chaque ojet dans le dossiers views/
if(!file_exists($database."/views/".$res[0]))
	mkdir($database."/views/".$res[0], 0777, true);

$req1=$cnx->query("SELECT * FROM information_schema.columns WHERE table_name = '".$res[0]."' AND table_schema='".$db_name."'");
$res1=$req1->fetchAll();

//generer controller
$fic=fopen($database."/models/".$res[0].".class.php", "w+");
$contenu="<?php
class ".$res[0]."{
//initialisation des attributs de l’objet voiture
";
foreach($res1 as $val){
$contenu.="private "."$".$val['COLUMN_NAME'].";
";
}
$contenu.="
//constructeur
";

$contenu.="public function __construct(";

$i=0;
foreach($res1 as $val){
$contenu.="$".$val['COLUMN_NAME'];
$i++;
if($i<count($res1))
$contenu.=",";
}
$contenu.=")
{
";
foreach($res1 as $val){
	$contenu.="	";
$contenu.="$"."this->".$val['COLUMN_NAME']."=$".$val['COLUMN_NAME'].";
";
}
$contenu.="}
";
$contenu.="
//methode d'ajout
";

$contenu.="public function ajout("."$"."cnx){
";
$contenu.="$"."cnx->exec(\"insert into ".$res[0]."(";
	
	$i=0;
foreach($res1 as $val){
$contenu.=$val['COLUMN_NAME'];
$i++;
if($i<count($res1))
$contenu.=",";
}
$contenu.=") values(";

$i=0;
$firstcalumn=0;
foreach($res1 as $val){

	if($res1[0]==$val){
		$firstcalumn=$val['COLUMN_NAME'];
	}
$contenu.=" '\"."."$"."this->".$val['COLUMN_NAME'].".\"'";
$i++;
if($i<count($res1))
$contenu.=",";
}
$contenu.=")\");
";

$contenu.="header(\"location:index.php?controller=".$res[0]."&action=liste\");
";
$contenu.="}
";
$contenu.="
//methode de selection
";
$contenu.="public function liste("."$"."cnx){
";
$contenu.="$"."resultat ="."$"."cnx->query(\"select * from ".$res[0]."\")->fetchAll(PDO::FETCH_OBJ);
";
$contenu.="return "."$"."resultat;
}";

$contenu.="
//methode de selection whre id 
";
$contenu.="public function listWhereId("."$"."cnx){
";
$contenu.="$"."resultat ="."$"."cnx->query(\"select * from ".$res[0];
$contenu.=" where ".$firstcalumn."='\"."."$"."this->".$firstcalumn.".\"'";
$contenu.="\")->fetch(PDO::FETCH_OBJ);
	 
";
$contenu.="return "."$"."resultat;
}";


$contenu.="
//methode de edit
";
$contenu.="public function edit("."$"."cnx){
";
$contenu.="$"."cnx->exec(\"update ".$res[0]." set ";

$i=1;
foreach($res1 as $val){
	if($val == $res1[0]){
		$ide=$val['COLUMN_NAME'];
	}
	if($val != $res1[0]){
$contenu.=$val['COLUMN_NAME']."="." '\"."."$"."this->".$val['COLUMN_NAME'].".\"'";

$i++;
if($i<count($res1))
$contenu.=",";
}
}
$contenu.=" where ".$ide."='\"."."$"."this->".$ide.".\"'";
$contenu.="\");
";
$contenu.="header(\"location:index.php?controller=".$res[0]."&action=liste\");
";
$contenu.="
}";

$contenu.="
//methode de delete
";
$contenu.="public function delete("."$"."cnx){
";
$contenu.="$"."cnx->exec(\"delete from ".$res[0]."";

$i=1;
foreach($res1 as $val){
	if($val == $res1[0]){
		$ide=$val['COLUMN_NAME'];
		//$contenu.=$val['COLUMN_NAME']."="." '\"."."$"."this->".$val['COLUMN_NAME'].".\"'";
	}

}
$contenu.=" where ".$ide."='\"."."$"."this->".$ide.".\"'";
$contenu.="\");
";
$contenu.="header(\"location:index.php?controller=".$res[0]."&action=liste\");
";
$contenu.="
}";
$contenu.="
}";
$contenu.="

?>";
fwrite($fic,$contenu,100000000);
fclose($fic); 
echo "<font color='green'>le model ".$res[0]." est créé avec succes.</font><br>";
}

echo ".........";
//generer les views
echo "<br>######################################################<br>
############### GENERATION DES VUES ###############<br>
######################################################
<br>";
$req=$cnx->query("SHOW TABLES");
//$req111=$req->fetch();
while($res=$req->fetch()){
//créer un dossier pour chaque ojet dans le dossiers views/
if(!file_exists($database."/views/".$res[0]))
	mkdir($database."/views/".$res[0], 0777, true);

$req1=$cnx->query("SELECT * FROM information_schema.columns WHERE table_name = '".$res[0]."' AND table_schema='".$db_name."'");
$res1=$req1->fetchAll();

//generer views
//view ajout
$fic=fopen($database."/views/".$res[0]."/ajout1.view.php", "w+");

$contenu="";
$contenu.="<form method=\"post\" action=\"index.php?controller=".$res[0]."&action=ajout\" enctype=\"multipart/form-data\">
";

foreach($res1 as $val){
$contenu.=" <label>".$val['COLUMN_NAME']."  ".$res[0]."</label>:";
$contenu.="<input type=\"txt\" name=\"".$val['COLUMN_NAME']."\">
";
}
$contenu.="<input type=\"submit\" value=\"ajouter\">";
$contenu.="</form>";

fwrite($fic,$contenu,100000000);
fclose($fic); 
echo "<font color='green'>le ajout view ".$res[0]." est créé avec succes.</font><br>";


//view liste
$fic=fopen($database."/views/".$res[0]."/liste.view.php", "w+");
$contenu="<table id=\"example1\" class=\"table table-bordered table-striped\">
";
$contenu.=" <thead>
";
$contenu.="<tr>
";

foreach($res1 as $val){
$contenu.="	<th>".$val['COLUMN_NAME']."</th>
";
}
$contenu.="	<th>action</th> 
";
$contenu.="</tr>
";
$contenu.="</thead>
";
$contenu.="<tbody>
";
$contenu.="<?php
";
$contenu.="foreach(\$res as \$obj){
";
$contenu.="?>
";
$contenu.="<tr>
";


foreach($res1 as $val){
	if($val == $res1[0]){
		$ide=$val['COLUMN_NAME'];
	}
$contenu.="	<td><?php echo \$obj->".$val['COLUMN_NAME'].";?></td>
";
}
$contenu.="<td><a href=\"index.php?controller=".$res[0]."&action=delete&".$ide."=<?php echo \$obj->".$ide.";?>\" onclick=\"if(confirm('etes vous sure de supprimer?')) return true; else return false;\">supp.</a>
";
$contenu.="| <a href=\"index.php?controller=".$res[0]."&action=edit1&".$ide."=<?php echo \$obj->".$ide.";?>\">modif.</a></td>";
$contenu.="</tr>
";
$contenu.="<?php 
}
?>
<script>
  \$(function () {
    \$('#example1').DataTable()
    \$('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>
</tbody>
</table>
";
fwrite($fic,$contenu,100000000);
fclose($fic); 
echo "<font color='green'>le liste ".$res[0]." est créé avec succes.</font><br>";


//view edite
$fic=fopen($database."/views/".$res[0]."/edit.view.php", "w+");
$contenu="<form method=\"post\" action=\"index.php?controller=".$res[0]."&action=edit\" enctype=\"multipart/form-data\">
";
$contenu.="<h1>Modifier".$res[0]."</h1>
";

foreach($res1 as $val){
$contenu.="<br>".$val['COLUMN_NAME']." <input type=\"text\" name=\"".$val['COLUMN_NAME']."\" value=\"<?php echo \$res->".$val['COLUMN_NAME'].";?>\">
";
}

$contenu.="<br><input type=\"submit\" value=\"Modifier ".$res[0]."\">
<input type=\"reset\" value=\"annuler\">
</form>
";

fwrite($fic,$contenu,100000000);
fclose($fic); 
echo "<font color='green'>le edit ".$res[0]." est créé avec succes.</font><br>";

//echo "1225fezf";


}
//creation index
$contenu="<html>
<header>
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">
<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css\">
  <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js\"></script>
  <script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\"></script>
  <script src=\"https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js\"></script>
</header>
<body>

";
$req=$cnx->query("SHOW TABLES");
//$req111=$req->fetch();
while($res=$req->fetch()){
//créer un dossier pour chaque ojet dans le dossiers views/
if(!file_exists($database."/views/".$res[0]))
	mkdir($database."/views/".$res[0], 0777, true);

$req1=$cnx->query("SELECT * FROM information_schema.columns WHERE table_name = '".$res[0]."' AND table_schema='".$db_name."'");
$res1=$req1->fetchAll();

//generer views
//view ajout
$fic=fopen($database."/index.php", "w+");


	
$contenu.="<a href=\"index.php?controller=".$res[0]."&action=liste\">liste ".$res[0]."</a>
";
}
$contenu.="<?php
    include \"include/connexion.php\";
    if (isset(\$_REQUEST['controller']))
    	\$controller = \$_REQUEST['controller'];
    if (isset(\$_REQUEST['action']))
      	\$action = \$_REQUEST['action'];
    if (isset(\$_REQUEST['action'])&&isset(\$_REQUEST['controller']))
        include \"controllers/\" . \$controller . \".controller.php\"
?>
		";
$contenu.="</body>
</html>

";
//$contenu.="<a href=\"index.php?controller=".$res[0]."&action=liste\">liste ".$res[0]."</a>";
fwrite($fic,$contenu,100000000);
fclose($fic); 
//echo "<font color='green'>le edit ".$res[0]." est créé avec succes.</font><br>";


//$fic=fopen("views/".$res[0]."/".$res[0].".delete.view.php", "w+");
}
echo ".........";
?>
