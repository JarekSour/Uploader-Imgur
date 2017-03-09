
<!DOCTYPE html>
<html lang="es">

<head>
    <title>InboxShot Uploader</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/bootswatch.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>

<body>
    <div id="alertCopied" class="bb-alert alert alert-info" style="display:none">
        <span>Url copiada!</span>
    </div>
    <div class="bootbox modal" id="dvLoading" style="display: none;background-color: rgba(70, 70, 70, 0.82);">
        <div class="col-md-6 col-md-offset-3">
            <center>
                <img src="img/logo.png" style="margin-top: 120px;" />
                <br>
                <i class="fa fa-spinner fa-pulse fa-5x" style="color: #FFF;margin-top: 50px;"></i>
                <br>
                <p style="color:#FFF;font-size: 18px;margin-top: 100px;">Subiendo imágenes...</p>
            </center>
        </div>
    </div>
    <div class="container">
        <h2 class="lead" style="margin-top: 3%;">Suba sus imágenes al servidor, y copie su link para ingresarlo a su campaña.</h2>
        <em>El peso máximo de imágenes es 1 MB</em>
        <br>
        <form id="myForm" method="post" enctype="multipart/form-data">
            <span class="btn btn-info btn-file">
              <i class="fa fa-plus"></i> Agregar imagenes 
              <input class="btn btn-default btn-file" type="file" id="files" name="files[]" multiple accept="image/*" />
          </span>
          <button class="btn btn-success" type="submit" id="btnLoadin" class="btn btn-default" name="btnSubmit"><i class="fa fa-cloud-upload"></i> Subir imagenes</button>
      </form>
      <div id="bar_blank" style="background-color:#03C">
        <div id="bar_color" style="background-color:#03C"></div>
    </div>
    <h3 id="status">
    </h3>

    <div class="container" style="margin-top:20px">
        <table class="table table-hover">
            <thead>
                <th>Vista previa</th>
                <th>Nombre</th>
                <th>Tamaño</th>
                <th>Ancho x Alto</th>
                <th>Link</th>
            </thead>
            <tbody id="tableImg">
                <?php
                if (isset($_POST['btnSubmit'])){
                  $sql = new mySql();

                  $valid_formats = array("jpg", "png", "gif", "jpeg");
                  $max_file_size = 1024*1024;

                  $total = count($_FILES['files']['name']);

                  if($total>0){

                      for($i=0; $i<$total; $i++){

                         $filename  = $_FILES['files']['tmp_name'][$i];
                         $client_id = "xxxxxxxxxxxxxxxxxxx";
                         $handle    = fopen($filename, "r");
                         $data 	   = fread($handle, filesize($filename));
                         $pvars     = array('image' => base64_encode($data));
                         $timeout   = 30;
                         $curl      = curl_init();
                         curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
                         curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
                         curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
                         curl_setopt($curl, CURLOPT_POST, 1);
                         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                         curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
                         $out = curl_exec($curl);
                         curl_close ($curl);
                         $pms = json_decode($out,true);
                         $url=$pms['data']['link'];

                         list($width, $height) = getimagesize($filename); 

                         if($url!=""){
                            echo "<tr>".
                            "<td><img style='width:90px;vertical-align: middle;' src='".$url."'/></td>".
                            "<td style='vertical-align: middle;'>".$_FILES['files']["name"][$i]."</td>".
                            "<td style='vertical-align: middle;'>".$_FILES['files']["size"][$i]."</td>".
                            "<td style='vertical-align: middle;'>".$width." x ".$height."</td>".
                            "<td style='vertical-align: middle;'>".
                            "<a class='btn btn-primary' onclick=\"copyToClipboard('".$url."')\"  >Copiar Url</a>".
                            "</td>".
                            "</tr>";
                        }else{
                           echo "<tr>".
                           "<td><img style='width:90px;vertical-align: middle;' src='".$filename."'/></td>".
                           "<td style='vertical-align: middle;'>".$_FILES['files']["name"][$i]."</td>".
                           "<td style='vertical-align: middle;'>".formatSizeUnits($_FILES['files']["size"][$i])."</td>".
                           "<td style='vertical-align: middle;'>".$width." x ".$height."</td>".
                           "<td style='vertical-align: middle;'>Ups! hubo un error</td>".
                           "</tr>";
                       }
                   }
               }
           }

           function formatSizeUnits($bytes){
            if ($bytes >= 1073741824)
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            elseif ($bytes >= 1048576)
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
            elseif ($bytes >= 1024)
                $bytes = number_format($bytes / 1024, 2) . ' KB';
            elseif ($bytes > 1)
                $bytes = $bytes . ' bytes';
            elseif ($bytes == 1)
                $bytes = $bytes . ' byte';
            else
                $bytes = '0 bytes';

            return $bytes;
        }
        ?>
    </tbody>
</table>
</div>
</div>

<script>
    function handleFileSelect(evt) {
        var files = evt.target.files;

        for (var i = 0, f; f = files[i]; i++) {

            if (!f.type.match('image.*'))
                continue;

            var reader = new FileReader();

            reader.onload = (function (theFile) {
                return function (e) {
                    var img = new Image();
                    img.src = e.target.result;
                    if (theFile.size <= 1048576) {
                        $("#tableImg").append("<tr id='" + theFile.name + "'><td><img style='width:90px;vertical-align: middle;' src='" + e.target.result + "' /></td><td style='vertical-align: middle;'>" + theFile.name + "</td><td style='vertical-align: middle;'>" + formatSizeUnits(theFile.size) + '</td><td style="vertical-align: middle;">' + img.width + " x " + img.height + '</td><td></td></tr>');
                    } else {
                        $("#tableImg").append("<tr id='" + theFile.name + "'><td><img style='width:90px;vertical-align: middle;' src='" + e.target.result + "' /></td><td style='vertical-align: middle;'>" + theFile.name + ' <i class="fa fa-exclamation-triangle text-danger" data-toggle="tooltip" data-placement="right" title="La imagen sobrepasa el tamaño limite admitido"></i></td><td style="vertical-align: middle;">' + formatSizeUnits(theFile.size) + '</td><td style="vertical-align: middle;">' + img.width + " x " + img.height + '</td><td></td></tr>');
                    }
                };
            })(f);
            reader.readAsDataURL(f);
        }
    }

    document.getElementById('files').addEventListener('change', handleFileSelect, false);

    function formatSizeUnits(bytes) {
        if (bytes >= 1000000000) {
            bytes = (bytes / 1000000000).toFixed(2) + ' GB';
        } else if (bytes >= 1000000) {
            bytes = (bytes / 1000000).toFixed(2) + ' MB';
        } else if (bytes >= 1000) {
            bytes = (bytes / 1000).toFixed(2) + ' KB';
        } else if (bytes > 1) {
            bytes = bytes + ' bytes';
        } else if (bytes == 1) {
            bytes = bytes + ' byte';
        } else {
            bytes = '0 byte';
        }
        return bytes;
    }

    function copyToClipboard(element) {
        $("#alertCopied").fadeIn(300).delay(2000).fadeOut(1000);
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(element).select();
        document.execCommand("copy");
        $temp.remove();
    }

    $(document).ready(function (e) {
        $("#btnLoadin").click(function (e) {
            $("#dvLoading").fadeIn(500);
        });
    });
</script>
</body>

</html>