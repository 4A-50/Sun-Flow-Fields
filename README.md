# Sun Flow Fields
## A Sun Based Flow Field Experiment

### What Is It?
Inspired after watching [The Beauty of Code: Flow Fields by Chris Courses](https://www.youtube.com/watch?v=na7LuZsW2UM) on YouTube, I wanted to experiment with flow fields to see what kind of mesmerising generative art I could produce. However, I didn't want to just make a copy of his (or the many other Flow Field experiments documented online), so I spent some time coming up with an idea that would make it my own, before remembering about a Sunrise & Sunset API I had used back in 2021.

### How I Made It

Before jumping into P5JS to work on the flow field I started on the PHP file the site would run from. I started by creating a CURL request to the Sunrise API which grabbed todays sun times at a specified location. I also implemented a Cookies system that stops the API from getting called every time the page is loaded/refreshed.

I then moved over to P5JS to begin working on the actual flow field. I started by following a similar approach to Chris' video where the canvas has a grid of points with a flow vector that pushes the nearest particles around. However this led to a lot of problems where particles would start to get stuck to the vectors and would just form little dots around where the vectors were initialised on the canvas.

After exploring and to find some insight into how and why this was happening, I came across a different approach where each particle is in charge off its direction each frame, but due to the Perlin Noise this produces the same effect but way simpler. So, I decided to switch to this method instead as, it worked straight away and was so much simpler to understand.

Now I had the two parts of the idea working I needed to combine them. This was easy as I could use PHP's 'Echo' function to inject the values into the P5JS script before its run by the browser. I then whipped up a quick function that worked out what current sun related times we were in between and the percentage split so the correct colours could be lerped between. I then spent some time watching the artwork evolve over a couple of minutes, before deciding there was something missing with it. If you wanted to see the entire day, the screen would be too full, and it wouldn't look as visually striking.

So, I quickly came up with a mode that would fly through an entire day at 300% and has slightly different opacities for the colours so the main blues and yellow are more evident than the greys and white of the nighttime. I then implemented the ability to save the canvas to file when either the user presses the 'S' key or when the daily mode has finished generating.

### View The Flow Fields
[Sun Flow Fields](https://flow.jakeprice.net)

![Noon Flow](https://jakeprice.net/images/FlowFields-01.jpg "Noon Flow")
![Sunrise Flow](https://jakeprice.net/images/FlowFields-02.jpg "Sunrise Flow")
![Civil Twilight Flow](https://jakeprice.net/images/FlowFields-03.jpg "Civil Twilight Flow")
![Nautical Twilight Flow](https://jakeprice.net/images/FlowFields-04.jpg "Nautical Twilight Flow")
![Astronomical Twilight Flow](https://jakeprice.net/images/FlowFields-05.jpg "Astronomical Twilight Flow")
![Night Flow](https://jakeprice.net/images/FlowFields-06.jpg "Night Flow")
