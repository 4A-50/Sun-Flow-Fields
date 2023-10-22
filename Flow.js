
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
let unixTime = 1697992433;

//Particle Array & Count
let particles = [];
let particleCount = 50;

//Noise Scale And Particle Direction
let noiseScale = 200
let direction = 1;

//If The Particles Should Be Lerping Colours
let lerpingCols = true;

//Target Times & Colours To Lerp Between
let targetTimes = [];
let targetColours = [];

//Sun Times And Colours
let sunTimes = [];
let sunColours = [];

//Current Mode
let mode = 0;

function setup() {
    //Sun Times From The API
    sunTimes = [ 1697949586,1697951881,1697954191,1697956116,1697974792,1697993467,1697995392,1697997703,1697999998];
    
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
    if (unixTime - 1698019200 >= 86400 && isLooping()) {
        saveCanvas('22.10.2023', 'jpg');
        noLoop();
    }
}
