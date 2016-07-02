<!DOCTYPE html>
<!-- Camera is a Pixedelic free jQuery slideshow | Manuel Masia (designer and developer) --> 
<html> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" > 
    <title>Camera | a free jQuery slideshow by Pixedelic</title> 
    <meta name="description" content="Camera a free jQuery slideshow with many effects, transitions, adaptive layout, easy to customize, using canvas and mobile ready"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--///////////////////////////////////////////////////////////////////////////////////////////////////
    //
    //		Styles
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////--> 
    <link rel='stylesheet' id='camera-css'  href='styles/camera.css' type='text/css' media='all'> 
    <style>		
		.fluid_container {
			margin: 0 auto;
			max-width: 1000px;
			width: 90%;
		}
	</style>

    <!--///////////////////////////////////////////////////////////////////////////////////////////////////
    //
    //		Scripts
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////--> 
    
    <script type='text/javascript' src='js/jquery.min.js'></script>
    <script type='text/javascript' src='js/jquery.mobile.customized.min.js'></script>
    <script type='text/javascript' src='js/jquery.easing.1.3.js'></script> 
    <script type='text/javascript' src='js/camera.min.js'></script> 
    
    <script>
		jQuery(function(){
			
			jQuery('#camera_wrap_1').camera({
                loader : 'bar',
                height: '30%',
                portrait: false,
                alignment: 'center'

			});
		});
	</script>
 
</head>
<body>
	
    
	<div class="fluid_container">
    
        <div class="camera_wrap camera_beige_skin" id="camera_wrap_1">
            <div data-src="images/poster1.jpg">
                <div class="camera_caption fadeFromBottom">
                    Informatics International College - Cainta Library
                </div>
            </div>
            <div data-src="images/library1.jpg">
                <div class="camera_caption fadeFromBottom">
                    Informatics International College - Cainta Library
                </div>
            </div>
            <div data-src="images/library2.jpg">
                <div class="camera_caption fadeFromBottom">
                    Informatics International College - Cainta Library
                </div>
            </div>
            <div data-src="images/library3.jpg">
                <div class="camera_caption fadeFromBottom">
                    Informatics International College - Cainta Library
                </div>
            </div>
        </div><!-- #camera_wrap_1 -->

    	
    </div><!-- .fluid_container -->

</body> 
</html>