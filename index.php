<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infinite Scrolling Cards</title>
    <style>
        #personal {
            position: relative;
            top: -33%;
            font-size: 20pt;
            text-align: center;

        }

        #personal h1 {
            font-size: 80pt;
            text-align: center;
        }

        #personal button {
            background-color: black;
            color: mediumblue;
            border: none;
            padding: 10px 20px;
            font-size: 20pt;
            cursor: pointer;
            margin-top: 20px;
        }

        #personal button:hover {
            background-color: mediumblue;
            color: black;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: mediumblue;
            color: black;
        }

        .sideblack {
            width: 25%;
            height: 100vh;
            background-color: black;
        }

        .content {
            width: 50%;
            height: 100vh;
            background-color: mediumblue;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .scroll-bar {
            width: 15%;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .scroll-bar.left {
            transform: translateX(-100%);
            /* Start off-screen to the left */
        }

        .scroll-bar.right {
            transform: translateX(100%);
            /* Start off-screen to the right */
        }

        .scroll-content {
            font-size: 62pt;
            display: flex;
            flex-direction: column;
            position: absolute;
            width: 100%;
            top: 0;
        }

        .card {
            text-align: center;
            font-size: 80pt;
            /* Increase the font size to make the cards bigger */
            margin: 0;
            /* Remove margin to eliminate space between cards */
            user-select: none;
            /* Make cards unselectable */
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
</head>

<body>
    <div class="sideblack"></div>
    <div class="content">
        <!-- Left Scroll Bar -->
        <div class="scroll-bar left">
            <div class="scroll-content" id="left-scroll">
                <?php
                // Define the cards
                $cards = [
                    0x1F0A1,
                    0x1F0A2,
                    0x1F0A3,
                    0x1F0A4,
                    0x1F0A5,
                    0x1F0A6,
                    0x1F0A7,
                    0x1F0A8,
                    0x1F0A9,
                    0x1F0AA,
                    0x1F0AB,
                    0x1F0AD,
                    0x1F0AE, // Spades
                    0x1F0B1,
                    0x1F0B2,
                    0x1F0B3,
                    0x1F0B4,
                    0x1F0B5,
                    0x1F0B6,
                    0x1F0B7,
                    0x1F0B8,
                    0x1F0B9,
                    0x1F0BA,
                    0x1F0BB,
                    0x1F0BD,
                    0x1F0BE, // Hearts
                    0x1F0C1,
                    0x1F0C2,
                    0x1F0C3,
                    0x1F0C4,
                    0x1F0C5,
                    0x1F0C6,
                    0x1F0C7,
                    0x1F0C8,
                    0x1F0C9,
                    0x1F0CA,
                    0x1F0CB,
                    0x1F0CD,
                    0x1F0CE, // Diamonds
                    0x1F0D1,
                    0x1F0D2,
                    0x1F0D3,
                    0x1F0D4,
                    0x1F0D5,
                    0x1F0D6,
                    0x1F0D7,
                    0x1F0D8,
                    0x1F0D9,
                    0x1F0DA,
                    0x1F0DB,
                    0x1F0DD,
                    0x1F0DE  // Clubs
                ];

                shuffle($cards); // Shuffle cards to randomize order

                // Output a large number of cards (e.g., 100) for the scroll content
                for ($i = 0; $i < 100; $i++) {
                    $card = $cards[$i % count($cards)];
                    echo "<div class='card'>" . mb_chr($card, 'UTF-8') . "</div>";
                }
                // Duplicate the cards to ensure seamless scrolling
                for ($i = 0; $i < 100; $i++) {
                    $card = $cards[$i % count($cards)];
                    echo "<div class='card'>" . mb_chr($card, 'UTF-8') . "</div>";
                }
                ?>
            </div>
        </div>

        <!-- Personal Information -->
        <div id="personal">
            <h1>Patron</h1>
            <p>Junior Software Developer</p>
            <p>Available from 19:15 to 21:30 as of current</p>
            <p>patron_librarian on Discord</p>
            <button>Contact Me</button> <!-- Add this line -->
        </div>

        <!-- Right Scroll Bar -->
        <div class="scroll-bar right">
            <div class="scroll-content" id="right-scroll">
                <?php
                shuffle($cards);
                for ($i = 0; $i < 100; $i++) {
                    $card = $cards[$i % count($cards)];
                    echo "<div class='card'>" . mb_chr($card, 'UTF-8') . "</div>";
                }
                // Duplicate the cards to ensure seamless scrolling
                for ($i = 0; $i < 100; $i++) {
                    $card = $cards[$i % count($cards)];
                    echo "<div class='card'>" . mb_chr($card, 'UTF-8') . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
    <div class="sideblack"></div>
    <script>
        anime({
            targets: '#left-scroll',
            translateY: ['0%', '-50%'],
            duration: 120000, // Increase duration to slow down the animation further
            easing: 'linear',
            loop: true
        });

        anime({
            targets: '#right-scroll',
            translateY: ['-50%', '0%'],
            duration: 120000, // Increase duration to slow down the animation further
            easing: 'linear',
            loop: true
        });

        // Animation for easing in the left side black bar
        anime({
            targets: '.sideblack:first-of-type',
            translateX: ['-100%', '0%'],
            duration: 2000,
            easing: 'easeOutQuad'
        });

        // Animation for easing in the right side black bar
        anime({
            targets: '.sideblack:last-of-type',
            translateX: ['100%', '0%'],
            duration: 2000,
            easing: 'easeOutQuad'
        });

        // Animation for easing in the content from the right
        anime({
            targets: '.content',
            translateX: ['100%', '0%'],
            duration: 2000,
            easing: 'easeOutQuad'
        });

        // Animation for easing in the left scroll bar from the left
        anime({
            targets: '.scroll-bar.left',
            translateX: ['-1500%', '0%'],
            duration: 2000,
            easing: 'easeOutQuad'
        });

        // Animation for easing in the right scroll bar from the right
        anime({
            targets: '.scroll-bar.right',
            translateX: ['100%', '0%'],
            duration: 2000,
            easing: 'easeOutQuad'
        });

        // Fade-in animation for personal information
        anime({
            targets: '#personal',
            opacity: [0, 1],
            duration: 2000,
            easing: 'easeInOutQuad',
            delay: 2000 // Add delay of 2.5 seconds
        });
    </script>
</body>

</html>