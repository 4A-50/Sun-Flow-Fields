class Particle {
    constructor(){
        this.pos = createVector(random() * windowWidth, random() * windowHeight);
        this.lastPos = this.pos;
        this.vel = createVector(0,0);
        this.speed = 1;
    }

    Update(){
        this.Move();
        this.CheckEdges();
        this.Show();
    }

    Move() {
        this.lastPos = this.pos;
      
        let newNoiseAngle = noise(this.pos.x / noiseScale,
                                  this.pos.y / noiseScale, 
                                  unixTime / noiseScale) * TWO_PI;
      
        this.vel = createVector(cos(newNoiseAngle), sin(newNoiseAngle));
        this.vel.mult(this.speed * direction);
        this.pos.add(this.vel);
    }

    Show(){
        line(this.pos.x, this.pos.y, this.lastPos.x, this.lastPos.y);
    }

    CheckEdges(){
        if (this.pos.x > windowWidth || this.pos.x < 0 ||
            this.pos.y > windowHeight || this.pos.y < 0){
          	this.pos.y = Math.random() * windowHeight;
          	this.pos.x = Math.random() * windowWidth;
        }
    }
}

let unixTime = <?php echo time() ?>;

let particles = [];
let particleCount = 50;

let noiseScale = 200
let direction = 1;

let lerpingCols = true;

let targetTimes = [];

let targetColours = [];

let sunTimes = [];

let sunColours = [];

function setup() {
    sunTimes = [ <?php echo strtotime($decodedJSON->results->astronomical_twilight_begin).",".
							strtotime($decodedJSON->results->nautical_twilight_begin).",".
							strtotime($decodedJSON->results->civil_twilight_begin).",".
							strtotime($decodedJSON->results->sunrise).",".
							strtotime($decodedJSON->results->solar_noon).",".
							strtotime($decodedJSON->results->sunset).",".
							strtotime($decodedJSON->results->civil_twilight_end).",".
							strtotime($decodedJSON->results->nautical_twilight_end).",".
							strtotime($decodedJSON->results->astronomical_twilight_end); ?>];
  
    sunColours = [color(180, 120, 130, 5),
                  color(255, 163, 172, 5),
                  color(255, 174, 151, 5),
                  color(255, 209, 86, 5),
                  color(135, 206, 235, 5),
                  color(255, 209, 86, 5),
                  color(255, 174, 151, 5),
                  color(255, 163, 172, 5),
                  color(180, 120, 130, 5)];
  
    targetTimes[0] = sunTimes[0];
    targetTimes[1] = sunTimes[1];
    targetColours[0] = sunColours[0];
    targetColours[1] = sunColours[1];
  
    BuildWindow();
}

function draw(){
    if(lerpingCols == true){
      let colourAmount = map(unixTime, targetTimes[0], targetTimes[1], 0, 1);
      stroke(lerpColor(targetColours[0], targetColours[1], colourAmount));
    }
  
  	for(let i = 0; i < particles.length; i++){
    	particles[i].Update();
  	}
  
    //Increments The Unix Time To Stay Up To Date With IRL Time
    if (frameCount % 60 == 0) {
        unixTime++;
        
        //Every 60 Seconds
        if(unixTime % 60 == 0){
          //Switch Flow Direction
          if(direction == 1){
              direction = -1;
          }
          else{
              direction = 1;
          }
          
          //And Check To Change Lerp Colours
          SetupColours();
        }
    }
}

function windowResized() {
    BuildWindow();
}

function BuildWindow(){
    resizeCanvas(windowWidth, windowHeight);
  
    background(17, 17, 17);
    strokeWeight(2);
  
    SetupColours();
  
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
        if(lerpingCols == false){
            lerpingCols = true;
        }

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
