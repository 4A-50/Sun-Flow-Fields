<?php
//Checks If The Client Has Cookie Saved
if(!isset($_COOKIE["Sun-Flow-Field"])) {
	//Sun Rise API URL
	$url = "https://api.sunrise-sunset.org/json?lat=51.111380&lng=1.158752";

	//CURL Setup
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	//Collect The CURL Response & Close The Connection
	$resp = curl_exec($curl);
	curl_close($curl);

	//Decode The JSON To Check The Status
	$decodedJSON = json_decode($resp);

	//If The Sun API Status Is Not Ok DIE
	if($decodedJSON->status != "OK")
	{
		die();
	}

	//Sets A Cookie With A 4 Hour Life To Avoid Spamming The Sun API
	setcookie("Sun-Flow-Field", $resp, time() + 14400, "/");
}
else{
	//If The Client Has A Cookie Set Its Value To The Decoded JSON Value
	$decodedJSON = json_decode($_COOKIE["Sun-Flow-Field"]);
}

//Init The Flow Mode
$flowMode = 0;

//Check If The Client Has Slected The Day Flow Mode
if(isset($_GET['m']) && !empty($_GET['m'])){
    if($_GET['m'] == "Day"){
        $flowMode = 1;
    }
}
?>

<html>

    <style>
		body{
			font-family: 'Work Sans', sans-serif;
		}

		h4, h5{
			margin: 10px;
		}

		a{
			color: #FFF;
		}

        #container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        #flowField{
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        #infoBox {
            position: fixed;
            bottom: 0;
			left: 0;
            z-index: 10;

			border-radius: 5px;

			color: #FFF;
			background-color: rgba(0, 0, 0, .25);
        }
    </style>

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
		<title>Sun Flow Fields</title>

        <!--Meta Data-->
		<meta content='Sun Flow Fields' property='og:title'>
		<meta content='A Sun Based Flow Field Experiment' property='og:description'>
		<meta content='flow.jakeprice.net' property='og:site_name'>
		<meta content='https://repository-images.githubusercontent.com/708134510/fcb3d1aa-e114-4333-a8a3-b97927284107' property='og:image'>
		<meta name="twitter:card" content="summary_large_image">
		<meta name="theme-color" content="#35b88f">

		<script src="https://cdn.jsdelivr.net/npm/p5@1.5.0/lib/p5.js"></script>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Work+Sans&display=swap" rel="stylesheet">
	</head>
	<body style="margin: 0px; padding: 0px">
		<script>
//Particle Class
class Particle {
	//Creates A New Particle With A Random Position
    constructor(){
        this.pos = createVector(random() * windowWidth, random() * windowHeight);
        this.lastPos = this.pos;
        this.vel = createVector(0,0);
        this.speed = 1;
    }

	//Moves -> Checks Its On Screen -> Displays Its Self Each Frame
    Update(){
        this.Move();
        this.CheckEdges();
        this.Show();
    }

	//Moves The Particle
    Move() {
		//Updates The Last Postion
        this.lastPos = this.pos;

		//Generates A New Angle Via The Perlin Noise From It's Current Pos
        let newNoiseAngle = noise(this.pos.x / noiseScale,
                                  this.pos.y / noiseScale) * TWO_PI;

        //Creates A New Vector From The The New Noise Angle
		this.vel = createVector(cos(newNoiseAngle), sin(newNoiseAngle));
		//Multiplies The Vector By Its Speed
        this.vel.mult(this.speed * direction);
		//Moves The Particle
        this.pos.add(this.vel);
    }

	//Displays The Particle
    Show(){
		//Draws A Line From Its Current Pos To Its New One
        line(this.pos.x, this.pos.y, this.lastPos.x, this.lastPos.y);
    }

	//Checks If The Particle Is On The Screen
    CheckEdges(){
        if (this.pos.x > windowWidth || this.pos.x < 0 ||
            this.pos.y > windowHeight || this.pos.y < 0){
			//If Its Not, Then Move It To A New Random Pos
          	this.pos.y = Math.random() * windowHeight;
          	this.pos.x = Math.random() * windowWidth;
        }
    }
}

//Starting Unix Time
let unixTime = <?php if($flowMode == 0)
                        echo time();
                     else
                        echo strtotime("00:00:00"); ?>;

//Particle Array & Count
let particles = [];
let particleCount = 50;

//Noise Scale And Particle Direction
let noiseScale = 200
let direction = <?php if(date("d") % 2 == 0)
                        echo 1;
                     else
                        echo -1; ?>;

//If The Particles Should Be Lerping Colours
let lerpingCols = true;

//Target Times & Colours To Lerp Between
let targetTimes = [];
let targetColours = [];

//Sun Times And Colours
let sunTimes = [];
let sunColours = [];

//Current Mode
let mode = <?php echo $flowMode ?>;

function setup() {
	//Sun Times From The API
    sunTimes = [ <?php echo strtotime($decodedJSON->results->astronomical_twilight_begin).",".
							strtotime($decodedJSON->results->nautical_twilight_begin).",".
							strtotime($decodedJSON->results->civil_twilight_begin).",".
							strtotime($decodedJSON->results->sunrise).",".
							strtotime($decodedJSON->results->solar_noon).",".
							strtotime($decodedJSON->results->sunset).",".
							strtotime($decodedJSON->results->civil_twilight_end).",".
							strtotime($decodedJSON->results->nautical_twilight_end).",".
							strtotime($decodedJSON->results->astronomical_twilight_end); ?>];

	//Sets The Colours Based On The Mode
    if(mode == 1){
		//Bump Up The Particle Count On Day Mode To Fill The Screen Quicker
        particleCount = 500;

        sunColours = [color(180, 120, 130, 10),
                  color(255, 163, 172, 15),
                  color(255, 174, 151, 20),
                  color(255, 209, 86, 25),
                  color(135, 206, 235, 60),
                  color(255, 209, 86, 25),
                  color(255, 174, 151, 20),
                  color(255, 163, 172, 15),
                  color(180, 120, 130, 10)];
    }
    else{
        sunColours = [color(180, 120, 130, 5),
                      color(255, 163, 172, 5),
                      color(255, 174, 151, 5),
                      color(255, 209, 86, 5),
                      color(135, 206, 235, 5),
                      color(255, 209, 86, 5),
                      color(255, 174, 151, 5),
                      color(255, 163, 172, 5),
                      color(180, 120, 130, 5)];
    }

	//Sets Starting Targets
    targetTimes[0] = sunTimes[0];
    targetTimes[1] = sunTimes[1];
    targetColours[0] = sunColours[0];
    targetColours[1] = sunColours[1];

	//Generates The Window
    BuildWindow();
}

function draw(){
	//If The Colours Are Lerping, Lerp Between The Two Targets
    if(lerpingCols == true){
      let colourAmount = map(unixTime, targetTimes[0], targetTimes[1], 0, 1);
      stroke(lerpColor(targetColours[0], targetColours[1], colourAmount));
    }

	//Update Each Particle
  	for(let i = 0; i < particles.length; i++){
    	particles[i].Update();
  	}

	//Deals With Mode Specific Functions
    switch (mode) {
        case 0:
            Mode0();
            break;
        case 1:
            Mode1();
            break;
        default:
            Mode0();
            break;
  }
}

function windowResized() {
	//Generates The Window
    BuildWindow();
}

function BuildWindow(){
	//Sets The Canvas To The Window Size
    resizeCanvas(windowWidth, windowHeight, true);

	//Sets The Background To A Dark Grey
    background(17, 17, 17);
	//Sets The Stroke Weight To 2
    strokeWeight(2);

	//Inits The Colours
    SetupColours();

	//Generate The Particles
    for (let i = 0; i < particleCount; i++){
      	particles.push(new Particle());
    }
}

function SetupColours(){
    //If It's Before Or After Sun Activity Set To Whitey-Grey For The Moon
    if(unixTime < sunTimes[0] || unixTime > sunTimes[8]){
        lerpingCols = false;
        stroke(200, 200, 200, 5);
    }
    //Else Find The Target Colours To Lerp Between
    else{
		//Makes Sure We Are Lerping The Colours In The Draw Loop
        if(lerpingCols == false){
            lerpingCols = true;
        }

		//Find The Two Targets We Are Currently Inbetween
        for(let i = 1; i < sunTimes.length; i++){
            if(unixTime >= sunTimes[i - 1] && unixTime <= sunTimes[i]){
                targetTimes[0] = sunTimes[i - 1];
                targetTimes[1] = sunTimes[i]

                targetColours[0] = sunColours[i - 1];
                targetColours[1] = sunColours[i];
            }
        }
    }
}

function Mode0(){
    //Increments The Unix Time To Stay Up To Date With IRL Time
    if (frameCount % 60 == 0) {
        unixTime ++;

        //Every 60 Seconds Check To Change Lerp Colours
        if(unixTime % 60 == 0){
          SetupColours();
        }
    }
}

function Mode1(){
	//Increments The Unix Time 300 Times A Frame
    unixTime += 300;

	//Checks The Lerp Colours Each Frame
    SetupColours();

	//If We've Drawn The Entire Day, Save The Frame And Stop
    if (unixTime - <?php echo strtotime("00:00:00 Tomorrow"); ?> >= 86400 && isLooping()) {
        saveCanvas('<?php echo date("d.m.Y"); ?>', 'jpg');
        noLoop();
    }
}

function keyPressed() {
    //Saves The Current Canvas If The User Preses The 'S' Key When In Realtime Mode
    if (key === 's' && mode != 1) {
        saveCanvas('<?php echo date("d.m.Y"); ?>', 'jpg');
    }
}
		</script>

        <div id="container">
            <div id="flowField">
				<main style="margin: 0px; padding: 0px;"></main>
			</div>
            <div id="infoBox">
                <h4>Sun Flow Fields</h4>
				<h5>
					A Sun Based Flow Field Experiment Programmed In P5JS & PHP<br/>
					<a href="index.php">Realtime Mode</a><br/>
					<a href="index.php?m=Day">Day Overiew Mode</a><br/>
					View The Code On <a href="https://github.com/4A-50/Sun-Flow-Fields" target="_blank">GitHub</a>
				</h5>
            </div>
        </div>


	</body>
</html>
