$('.lottery-carousel').owlCarousel({
  loop:false,
  slideBy: 3,
  margin:10,
  responsiveClass:true,
  dots:false,
  infinityLoop:true,
  items:3,
  responsive:{
      0:{
          items:1,
          nav:true
      },
      600:{
          items:3,
          nav:false
      },
      1000:{
          items:3,
          nav:true,
          loop:false
      }
  }
})


// var button = document.getElementById("generate");

        // button.addEventListener("click", function () {
        //     var numbers = [];
        //     var ball = document.getElementsByTagName("label");

        //     while (numbers.length < 6) {

        //         var random = Math.floor(Math.random() * 49) + 1;

        //         if (numbers.indexOf(random) == -1) {
        //             numbers.push(random);
        //         }
        //     }
            
        //     numbers.sort(function (a, b) {
        //         return a - b;
        //     });
        //     for (var i = 0; i < ball.length; i++) {

        //         ball[i].style.backgroundColor = "white";
        //         ball[i].style.color = "#000";

        //         for (var j = 0; j < numbers.length; j++) {
        //             if (numbers[j] == parseInt(ball[i].innerHTML)) {
        //                 ball[i].style.backgroundColor = "#fa8b60";
        //                 ball[i].style.color = "white";
        //             }
        //         }
        //     }

        //     var history = [];
        //     var historyDiv = document.getElementById("history");
        //     var para = document.createElement("li");
        //     history.push(numbers.join(", "));

        //     for (var k in history) {

        //         var node = document.createTextNode(history[k]);
        //         para.appendChild(node);
        //         historyDiv.appendChild(para);

        //     }

        // });



