<?php
/* [PUBLIC-LANDING] - HIGH-ENERGY CREATIVE TECHNOLOGIST */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es" class="bg-black text-slate-100 antialiased" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jahzael Reyes | Creative Technologist</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Space+Grotesk:wght@300;400;600;700&family=Inter:wght@300;400;600;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #000;
            cursor: none;
            overflow-x: hidden;
        }
        .font-sync { font-family: 'Syncopate', sans-serif; }
        .font-inter { font-family: 'Inter', sans-serif; }

        /* Loader */
        #loader {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        /* Cursor */
        .cursor-dot {
            position: fixed; top: 0; left: 0; transform: translate(-50%, -50%);
            width: 10px; height: 10px; background: #fff; border-radius: 50%;
            z-index: 9999; pointer-events: none; mix-blend-mode: difference;
        }
        .cursor-outline {
            position: fixed; top: 0; left: 0; transform: translate(-50%, -50%);
            width: 40px; height: 40px; border: 1px solid rgba(255,255,255,0.5);
            border-radius: 50%; z-index: 9998; pointer-events: none;
            transition: width 0.3s, height 0.3s, background 0.3s;
            mix-blend-mode: difference;
        }

        /* Abstract Fluid Gradient Background */
        .fluid-bg {
            position: fixed;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 50% 50%, rgba(234, 88, 12, 0.15), transparent 40%),
                        radial-gradient(circle at 80% 20%, rgba(147, 51, 234, 0.15), transparent 40%),
                        radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.15), transparent 40%);
            z-index: 0; pointer-events: none;
            animation: fluidRotate 20s linear infinite;
        }
        @keyframes fluidRotate {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }

        /* Marquee */
        .marquee {
            white-space: nowrap; overflow: hidden; position: absolute;
            width: 100vw; font-family: 'Syncopate', sans-serif;
            opacity: 0.05; font-size: 10vw; font-weight: 900;
            pointer-events: none; user-select: none; z-index: 0;
        }
        .marquee-inner { display: inline-block; animation: marqueeScroll 20s linear infinite; }
        .marquee-reverse { animation: marqueeScrollRev 25s linear infinite; }
        @keyframes marqueeScroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes marqueeScrollRev { 0% { transform: translateX(-50%); } 100% { transform: translateX(0); } }

        /* Scene / Tech UI Borders */
        .tech-border {
            position: relative;
        }
        .tech-border::before, .tech-border::after {
            content: ''; position: absolute; width: 10px; height: 10px; border: 2px solid #ea580c;
        }
        .tech-border::before { top: -2px; left: -2px; border-right: none; border-bottom: none; }
        .tech-border::after { bottom: -2px; right: -2px; border-left: none; border-top: none; }

        /* Hover Reveal Images */
        .hover-image-reveal {
            position: absolute; width: 400px; height: 500px;
            object-fit: cover; opacity: 0; pointer-events: none; z-index: 50;
            transform: translate(-50%, -50%) scale(0.8);
            border-radius: 12px; filter: grayscale(100%) contrast(1.2);
            transition: filter 0.3s;
        }
        .hover-trigger:hover ~ .hover-image-reveal { filter: grayscale(0%) contrast(1.1); }

        .blend-diff { mix-blend-mode: difference; }

        /* Crosshairs grid background */
        .bg-grid {
            background-size: 100px 100px;
            background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #ea580c; }
    </style>
</head>
<body class="bg-grid">

    <!-- Preloader -->
    <div id="loader">
        <div class="font-sync text-6xl md:text-9xl font-black text-transparent stroke-text" style="-webkit-text-stroke: 2px #ea580c;" id="loader-counter">0%</div>
        <div class="text-xs tracking-[0.4em] uppercase mt-4 text-slate-500 font-sync">Initializing Core...</div>
    </div>

    <!-- Cursors -->
    <div class="cursor-dot hidden md:block"></div>
    <div class="cursor-outline hidden md:block"></div>
    <img src="" id="hover-follower" class="hover-image-reveal hidden md:block">

    <div class="fluid-bg"></div>

    <!-- Nav -->
    <header class="fixed top-0 w-full p-6 z-50 flex justify-between items-center mix-blend-difference text-white">
        <div class="font-sync font-bold tracking-[0.2em] text-sm uppercase">JAHZAEL REYES <span class="text-orange-500 ml-2">///</span></div>
        <a href="mailto:info@jahzaelreyes.com.ar" class="text-xs font-sync tracking-widest uppercase cursor-interaction border border-white/20 px-4 py-2 rounded-full hover:bg-white hover:text-black transition-all">Start Project</a>
    </header>

    <main id="smooth-wrapper">
        <div id="smooth-content" class="relative z-10">

            <!-- HERO -->
            <section class="min-h-screen relative flex items-center justify-center overflow-hidden pt-20" id="hero">
                <!-- Infinite Background Marquees -->
                <div class="marquee top-[20%] text-orange-500"><div class="marquee-inner">CREATIVE TECHNOLOGIST — CREATIVE TECHNOLOGIST — CREATIVE TECHNOLOGIST — </div></div>
                <div class="marquee top-[50%] text-purple-500"><div class="marquee-inner marquee-reverse">ART DIRECTION — 3D PRINTING — ART DIRECTION — 3D PRINTING — </div></div>
                <div class="marquee top-[80%] text-blue-500"><div class="marquee-inner">AI AGENTS — STREAMING — AI AGENTS — STREAMING — AI AGENTS — </div></div>

                <div class="text-center z-20 w-full px-6 mix-blend-difference">
                    <h1 class="text-[12vw] font-inter font-black uppercase leading-[0.8] tracking-tighter text-white" id="gravity-title">
                        <span class="block overflow-visible"><span class="hero-text-line block gravity-word">Creative</span></span>
                        <span class="block overflow-visible"><span class="hero-text-line block text-orange-500 italic gravity-word">Technologist.</span></span>
                    </h1>
                    <div class="mt-12 flex justify-center">
                        <p class="text-slate-300 max-w-lg text-sm md:text-base font-light tracking-wide text-left border-l-2 border-orange-500 pl-6 hero-desc">
                            10 años forjando realidades. Desde dirección de arte visual hasta fabricación aditiva técnica e inteligencia autónoma. No somos un producto masivo. Somos la vanguardia.
                        </p>
                    </div>
                </div>

                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 text-xs font-sync tracking-[0.3em] uppercase text-white/50 animate-bounce">
                    Scroll Down
                </div>
            </section>

            <!-- MEDIA / HORIZONTAL SCROLL SCENE -->
            <section class="h-screen w-full relative overflow-hidden bg-white text-black" id="horizontal-scroll">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2000')] bg-cover bg-center opacity-10 mix-blend-multiply"></div>
                <div class="flex h-full items-center w-[300vw]" id="horizontal-container">
                    
                    <div class="w-screen h-full flex items-center justify-center px-10 relative">
                        <div class="max-w-4xl w-full">
                            <span class="font-sync text-orange-600 text-sm tracking-[0.2em] font-bold block mb-4">01 // VISUAL DNA</span>
                            <h2 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tighter">10 Years<br>Of Craft.</h2>
                            <p class="mt-6 text-xl max-w-xl font-medium">Diseño, fotografía y cinematografía. Cada pixel tiene una intención estratégica. No relleno, puro impacto.</p>
                        </div>
                    </div>
                    
                    <div class="w-screen h-full flex items-center justify-center px-10 relative bg-black text-white">
                        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1581092160562-40aa08e78837?q=80&w=2000')] bg-cover bg-center opacity-30"></div>
                        <div class="max-w-4xl w-full relative z-10">
                            <span class="font-sync text-blue-500 text-sm tracking-[0.2em] font-bold block mb-4">02 // PHYSICAL MATTER</span>
                            <h2 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tighter">Advanced<br>3D Fab.</h2>
                            <p class="mt-6 text-xl max-w-xl text-slate-300 font-light">Polímeros de ingeniería. Tolerancia cero. Transformando polígonos virtuales en materia táctil a través de granjas de impresión industriales.</p>
                        </div>
                    </div>

                    <div class="w-screen h-full flex items-center justify-center px-10 relative bg-purple-900 text-white">
                        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1620712943543-bcc4688e7485?q=80&w=2000')] bg-cover bg-center opacity-40 mix-blend-overlay"></div>
                        <div class="max-w-4xl w-full relative z-10">
                            <span class="font-sync text-purple-300 text-sm tracking-[0.2em] font-bold block mb-4">03 // AUTONOMOUS</span>
                            <h2 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tighter">AI Agents &<br>Pipelines.</h2>
                            <p class="mt-6 text-xl max-w-xl font-light">Optimización extrema para creadores. Setup de Streaming, Gaming y flujos de trabajo manejados por Inteligencia Artificial. Multiplica tu output.</p>
                        </div>
                    </div>

                </div>
            </section>

            <!-- THE MINDSET HOVER REVEAL -->
            <section class="py-40 bg-black relative border-t border-slate-900" id="mindset">
                <div class="max-w-6xl mx-auto px-6">
                    <div class="text-center mb-24">
                        <h2 class="font-sync text-orange-500 text-sm tracking-[0.3em] uppercase mb-4">The Duality</h2>
                        <h3 class="text-4xl md:text-6xl font-black text-white">ADRENALINA & ZEN</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Card 1 -->
                        <div class="hover-trigger relative group tech-border p-10 cursor-interaction bg-slate-950/50 backdrop-blur-md" data-image="https://images.unsplash.com/photo-1551698618-1dfe5d97d256?q=80&w=1000">
                            <div class="absolute inset-0 bg-orange-600/10 scale-y-0 group-hover:scale-y-100 origin-bottom transition-transform duration-500"></div>
                            <h4 class="text-3xl font-black text-white mb-4 relative z-10 font-sync">Extreme<br>Sports</h4>
                            <p class="text-slate-400 relative z-10 leading-relaxed font-light">Velocidad. Reflejos. Riesgo. El deporte extremo entrena la mente para tomar decisiones en fracciones de segundo. Ejecutar sin margen para dudar.</p>
                        </div>
                        
                        <!-- Card 2 -->
                        <div class="hover-trigger relative group tech-border p-10 cursor-interaction bg-slate-950/50 backdrop-blur-md" data-image="https://images.unsplash.com/photo-1545389336-cf090694435e?q=80&w=1000">
                            <div class="absolute inset-0 bg-blue-600/10 scale-y-0 group-hover:scale-y-100 origin-bottom transition-transform duration-500"></div>
                            <h4 class="text-3xl font-black text-white mb-4 relative z-10 font-sync">Mindful<br>Meditation</h4>
                            <p class="text-slate-400 relative z-10 leading-relaxed font-light">Claridad. Foco quirúrgico. La capacidad de sumergirse en el detalle invisible de cada proyecto, encontrando la calma en el caos de la producción.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- GIANT FOOTER -->
            <footer class="h-screen bg-[#ea580c] text-black flex flex-col justify-center items-center relative overflow-hidden" id="footer">
                <div class="marquee top-[10%] text-black opacity-20"><div class="marquee-inner">NO MASIVO. NO GENÉRICO. ARTESANO DIGITAL — </div></div>
                <div class="marquee bottom-[10%] text-black opacity-20"><div class="marquee-inner marquee-reverse">HIGH PERFORMANCE — HIGH PERFORMANCE — </div></div>
                
                <h2 class="text-[8vw] font-black uppercase text-center leading-[0.8] tracking-tighter mb-12 hover:scale-105 transition-transform duration-500 cursor-interaction mix-blend-difference text-white">
                    Let's Build<br>The Future.
                </h2>
                
                <a href="mailto:info@jahzaelreyes.com.ar" class="border-2 border-black px-12 py-6 rounded-full font-sync font-bold tracking-widest text-lg hover:bg-black hover:text-white transition-all cursor-interaction">
                    INICIAR PROYECTO
                </a>

                <div class="absolute bottom-6 w-full text-center font-mono text-sm font-bold opacity-50 flex flex-col gap-2">
                    <span>© <?= date('Y') ?> JAHZAEL REYES</span>
                    <a href="/hub-core-kx92/" class="hover:underline cursor-interaction">Workstation Access</a>
                </div>
            </footer>

        </div>
    </main>

    <script>
        // --- CUSTOM CURSOR & HOVER IMAGE REVEAL ---
        const cursorDot = document.querySelector('.cursor-dot');
        const cursorOutline = document.querySelector('.cursor-outline');
        const hoverFollower = document.getElementById('hover-follower');
        
        if(window.matchMedia("(pointer: fine)").matches) {
            window.addEventListener('mousemove', (e) => {
                gsap.to(cursorDot, { x: e.clientX, y: e.clientY, duration: 0.05 });
                gsap.to(cursorOutline, { x: e.clientX, y: e.clientY, duration: 0.3, ease: "power2.out" });
                
                // Follower image logic
                if (hoverFollower.style.opacity > 0) {
                    gsap.to(hoverFollower, { x: e.clientX, y: e.clientY, duration: 0.6, ease: "power3.out" });
                }
            });

            document.querySelectorAll('a, button, .cursor-interaction').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    gsap.to(cursorOutline, { scale: 2, borderColor: '#ea580c', backgroundColor: 'rgba(234, 88, 12, 0.1)', duration: 0.2 });
                });
                el.addEventListener('mouseleave', () => {
                    gsap.to(cursorOutline, { scale: 1, borderColor: 'rgba(255, 255, 255, 0.5)', backgroundColor: 'transparent', duration: 0.2 });
                });
            });

            // Image Reveal Hover
            document.querySelectorAll('.hover-trigger').forEach(el => {
                el.addEventListener('mouseenter', (e) => {
                    const imgUrl = el.getAttribute('data-image');
                    hoverFollower.src = imgUrl;
                    gsap.to(hoverFollower, { opacity: 0.6, scale: 1, duration: 0.4, x: e.clientX, y: e.clientY });
                });
                el.addEventListener('mouseleave', () => {
                    gsap.to(hoverFollower, { opacity: 0, scale: 0.8, duration: 0.4 });
                });
            });
        }

        // --- PRELOADER ---
        let counter = { val: 0 };
        gsap.to(counter, {
            val: 100,
            duration: 2,
            ease: "power4.inOut",
            onUpdate: function() {
                document.getElementById('loader-counter').innerText = Math.round(counter.val) + "%";
            },
            onComplete: function() {
                gsap.to("#loader", {
                    yPercent: -100,
                    duration: 1,
                    ease: "power4.inOut",
                    onComplete: initScrollAnimations
                });
            }
        });

        // --- GSAP SCROLLTRIGGER ---
        gsap.registerPlugin(ScrollTrigger);

        function initScrollAnimations() {
            // Dividir las palabras en letras para el efecto gravedad
            document.querySelectorAll('.gravity-word').forEach(word => {
                const text = word.innerText;
                word.innerHTML = text.split('').map(char => `<span class="gravity-letter inline-block hover:text-red-500 cursor-none transition-colors duration-300 pointer-events-auto">${char === ' ' ? '&nbsp;' : char}</span>`).join('');
            });

            // Animación inicial del hero
            gsap.from(".hero-text-line", {
                y: 200, rotation: 10, duration: 1.2, stagger: 0.2, ease: "power4.out"
            });
            gsap.from(".hero-desc", {
                opacity: 0, x: -50, duration: 1, delay: 0.8, ease: "power3.out"
            });

            // Configurar el efecto de gravedad al scrollear hacia abajo
            let gravityTriggered = false;
            ScrollTrigger.create({
                trigger: "#horizontal-scroll",
                start: "top bottom", // Se activa cuando la siguiente sección toca abajo
                onEnter: () => {
                    if(gravityTriggered) return;
                    gravityTriggered = true;

                    const letters = document.querySelectorAll('.gravity-letter');
                    letters.forEach((letter) => {
                        const rect = letter.getBoundingClientRect();
                        
                        // Clonar la letra como elemento flotante independiente
                        const ghost = document.createElement('span');
                        ghost.innerHTML = letter.innerHTML;
                        ghost.className = 'gravity-letter-ghost fixed font-inter font-black uppercase text-[12vw] leading-[0.8] tracking-tighter text-white/80 pointer-events-auto cursor-none hover:text-red-500 transition-colors z-[10000]';
                        ghost.style.left = rect.left + 'px';
                        ghost.style.top = rect.top + 'px';
                        document.body.appendChild(ghost);

                        // Ocultar letra original
                        letter.style.opacity = '0';

                        // Animar caída al fondo de la pantalla (Gravedad)
                        const dropY = window.innerHeight - rect.top - (rect.height * 0.8) - (Math.random() * 40);
                        const randomRot = (Math.random() - 0.5) * 60;
                        const randomX = (Math.random() - 0.5) * 100;

                        gsap.to(ghost, {
                            y: dropY,
                            x: randomX,
                            rotation: randomRot,
                            duration: 1 + Math.random(),
                            ease: "bounce.out"
                        });

                        // Eliminar con el ratón (Hover Eraser)
                        ghost.addEventListener('mouseenter', () => {
                            gsap.to(ghost, {
                                scale: 0, opacity: 0, rotation: randomRot + 180,
                                duration: 0.4, ease: "back.in(2)",
                                onComplete: () => ghost.remove()
                            });
                        });
                    });
                }
            });

            // Horizontal Scroll Section
            const horizContainer = document.getElementById("horizontal-container");
            gsap.to(horizContainer, {
                xPercent: -66.666,
                ease: "none",
                scrollTrigger: {
                    trigger: "#horizontal-scroll",
                    pin: true,
                    scrub: 1,
                    end: "+=3000" // Controla la duración del scroll horizontal
                }
            });

            // Explosión de letras si sobrevivieron al llegar al siguiente acto (Mindset)
            ScrollTrigger.create({
                trigger: "#mindset",
                start: "top 80%", // Cuando la sección Mindset entra en el 80% de la pantalla (justo después del scroll horizontal)
                onEnter: () => {
                    const survivingLetters = document.querySelectorAll('.gravity-letter-ghost');
                    if(survivingLetters.length > 0) {
                        
                        // Flashazo de fondo
                        const flash = document.createElement('div');
                        flash.style.cssText = "position:fixed; inset:0; background:white; z-index:99999; pointer-events:none; mix-blend-mode:difference;";
                        document.body.appendChild(flash);
                        gsap.to(flash, { opacity: 0, duration: 0.5, ease: "power2.out", onComplete: () => flash.remove() });

                        // Explosión
                        survivingLetters.forEach(ghost => {
                            const angle = Math.random() * Math.PI * 2;
                            const velocity = 800 + Math.random() * 1500;
                            const explodeX = Math.cos(angle) * velocity;
                            const explodeY = Math.sin(angle) * velocity;
                            
                            // Efecto metralla/bomba
                            gsap.to(ghost, {
                                color: "#ea580c", // Naranja fuego
                                scale: 2 + Math.random() * 3,
                                x: `+=${explodeX}`,
                                y: `+=${explodeY}`,
                                rotation: (Math.random() - 0.5) * 1500,
                                opacity: 0,
                                duration: 0.8 + Math.random() * 0.5,
                                ease: "expo.out",
                                onComplete: () => ghost.remove()
                            });
                        });
                    }
                }
            });

            // Footer Reveal effect
            gsap.from("#footer h2", {
                scrollTrigger: {
                    trigger: "#footer",
                    start: "top 80%",
                },
                y: 100,
                opacity: 0,
                duration: 1,
                ease: "back.out(1.7)"
            });
        }
    </script>
</body>
</html>
