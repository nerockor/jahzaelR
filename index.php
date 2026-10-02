<?php
/* [PUBLIC-LANDING] - THE CREATIVE TECHNOLOGIST MANIFESTO */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es" class="bg-slate-950 text-slate-100 antialiased" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jahzael Reyes | Creative Technologist & Director</title>
    <meta name="description" content="Estudio de Dirección de Arte, Fabricación 3D de Precisión e Inteligencia Artificial.">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- GSAP & ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Inter:wght@300;400;600;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* slate-950 */
            cursor: none; /* Oculta cursor default para usar el magnético */
            overflow-x: hidden;
        }

        .font-sync {
            font-family: 'Syncopate', sans-serif;
        }

        /* Magnetic Custom Cursor */
        .cursor-dot, .cursor-outline {
            position: fixed;
            top: 0; left: 0;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            z-index: 9999;
            pointer-events: none;
        }
        .cursor-dot {
            width: 8px; height: 8px;
            background-color: white;
            mix-blend-mode: difference;
        }
        .cursor-outline {
            width: 40px; height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            transition: width 0.2s, height 0.2s, background-color 0.2s;
            mix-blend-mode: difference;
        }

        /* Mix Blend Modes & Glowing Orbs */
        .blend-diff { mix-blend-mode: difference; color: #fff; }
        .blend-screen { mix-blend-mode: screen; }
        
        .ambient-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
            will-change: transform;
        }

        /* SCENES */
        .scene {
            min-height: 100vh;
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* Oculta scrollbar en navegadores para un look mas limpio */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    </style>
</head>
<body class="relative text-slate-200">

    <!-- Custom Cursor Nodes -->
    <div class="cursor-dot hidden md:block"></div>
    <div class="cursor-outline hidden md:block"></div>

    <!-- Nav (Difference mode) -->
    <header class="fixed top-0 w-full p-6 z-50 flex justify-between items-center blend-diff">
        <div class="font-sync font-bold tracking-widest text-sm uppercase">JAHZAEL REYES</div>
        <a href="mailto:info@jahzaelreyes.com.ar" class="text-xs font-sync tracking-widest uppercase hover:opacity-70 transition cursor-interaction">Colaborar</a>
    </header>

    <!-- MAIN SCROLL CONTAINER -->
    <main id="smooth-wrapper">
        <div id="smooth-content">

            <!-- HERO SCENE -->
            <section class="scene flex-col text-center z-10" id="hero">
                <!-- Luz ambiental dinámica -->
                <div class="ambient-glow bg-emerald-600 w-[600px] h-[600px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 blend-screen"></div>
                
                <h1 class="hero-title text-6xl md:text-8xl lg:text-[10rem] font-black tracking-tighter z-10 uppercase leading-none blend-diff text-white" style="line-height: 0.85;">
                    Creative<br>Technologist
                </h1>
                <p class="hero-sub text-slate-400 mt-10 max-w-xl mx-auto text-sm md:text-base font-light tracking-wide z-10">
                    Art Direction. Precision 3D Fabrication. Autonomous AI Systems. 
                    <br><br>10 años transformando instinto puro en estructuras de la nueva era.
                </p>
                <div class="mt-16 text-[10px] uppercase font-sync tracking-[0.3em] text-slate-500 animate-pulse z-10">
                    [ Scroll to Explore ]
                </div>
            </section>

            <!-- ACT 01: THE ORIGIN (10 YRS MULTIMEDIA) -->
            <section class="scene bg-slate-900 z-20 py-20" id="act-1">
                <div class="max-w-7xl w-full px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">
                    <div class="act1-text">
                        <span class="font-sync text-orange-500 text-xs tracking-[0.2em] uppercase block mb-4">01. The Craft</span>
                        <h2 class="text-5xl md:text-7xl font-black mb-6 leading-tight blend-diff text-white">
                            A Decade of<br>Visual Art.
                        </h2>
                        <p class="text-slate-400 font-light leading-relaxed mb-6 max-w-md">
                            Diseño gráfico editorial, branding corporativo multimedia y cinematografía.<br>No es solo estética; es comunicación estratégica y funcional. Dominio absoluto de la luz, el lente y el píxel, forjado tras miles de horas de dirección de arte.
                        </p>
                    </div>
                    <div class="act1-visual h-[400px] md:h-[600px] relative glass-panel rounded-3xl overflow-hidden flex items-center justify-center cursor-interaction">
                        <div class="absolute w-full h-full bg-gradient-to-br from-orange-900/40 to-slate-900 mix-blend-overlay"></div>
                        <!-- Círculo geométrico como representación abstracta -->
                        <div class="w-48 h-48 border border-orange-500/30 rounded-full animate-[spin_20s_linear_infinite] flex items-center justify-center">
                            <div class="w-32 h-32 border border-orange-500/50 rounded-full"></div>
                        </div>
                        <h3 class="absolute text-[8rem] font-black text-white/5 font-sync select-none">PIXEL</h3>
                    </div>
                </div>
            </section>

            <!-- ACT 02: PHYSICAL MATTER (3D & POLYMERS) -->
            <section class="scene bg-black z-30 py-20" id="act-2">
                <div class="ambient-glow bg-blue-600/30 w-[800px] h-[800px] -right-[200px] top-0 blend-screen"></div>
                <div class="max-w-7xl w-full px-6 relative z-10 flex flex-col md:flex-row-reverse gap-12 items-center">
                    <div class="act2-text md:w-1/2">
                        <span class="font-sync text-blue-500 text-xs tracking-[0.2em] uppercase block mb-4">02. Physical Matter</span>
                        <h2 class="text-5xl md:text-7xl font-black mb-6 leading-tight text-white">
                            Beyond<br>The Screen.
                        </h2>
                        <p class="text-slate-400 font-light leading-relaxed max-w-md">
                            Del átomo digital al polímero de ingeniería. Fabricación aditiva de alto rendimiento (PETG, ABS, PPS-CF). <br><br>Operación de granjas 3D industriales para prototipado técnico, piezas mecánicas funcionales y escultura de precisión. 
                            <br><br><span class="text-white font-medium">Cero tolerancias. Ejecución milimétrica.</span>
                        </p>
                    </div>
                    <div class="act2-visual md:w-1/2 h-[400px] md:h-[600px] relative flex items-center justify-center cursor-interaction">
                        <!-- Abstract 3D mesh wireframe representation -->
                        <div class="w-full h-full border border-blue-500/10 rounded-[3rem] flex items-center justify-center animate-[spin_60s_linear_infinite] rotate-45">
                            <div class="w-3/4 h-3/4 border border-blue-400/20 rounded-[2rem] flex items-center justify-center animate-[spin_40s_linear_infinite_reverse]">
                                <div class="w-1/2 h-1/2 border border-blue-300/30 rounded-[1rem] mix-blend-screen bg-blue-900/10 backdrop-blur-3xl"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ACT 03: SYNTHETIC MIND (AI / GAMING / STREAMING) -->
            <section class="scene bg-slate-950 z-40 relative" id="act-3">
                <div class="ambient-glow bg-purple-600/30 w-[1000px] h-[600px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 blend-screen"></div>
                
                <div class="w-full max-w-5xl mx-auto px-6 text-center z-10 relative">
                    <span class="font-sync text-purple-500 text-xs tracking-[0.2em] uppercase block mb-6">03. Autonomous Intelligence</span>
                    <h2 class="act3-title text-6xl md:text-8xl font-black mb-8 leading-tight text-white blend-diff">
                        AI Agents &<br>Pipelines.
                    </h2>
                    <p class="act3-desc text-slate-400 max-w-2xl mx-auto font-light leading-relaxed text-lg">
                        Ingeniería de sistemas autónomos y cultura interactiva. Generación de entornos inmersivos para Gaming y Live Streaming. Agentes de IA que no solo asisten, sino que <span class="text-purple-400 font-semibold">multiplican</span> el rendimiento creativo y optimizan la entrega audiovisual en tiempo real.
                    </p>
                </div>
            </section>

            <!-- ACT 04: THE MINDSET (ADRENALINA & ZEN) -->
            <section class="min-h-screen bg-slate-100 z-50 text-slate-950 relative" id="act-4">
                <div class="w-full h-full flex flex-col md:flex-row min-h-screen">
                    
                    <!-- Adrenalina -->
                    <div class="w-full md:w-1/2 flex flex-col justify-center p-12 md:p-24 border-b md:border-b-0 md:border-r border-slate-300 relative overflow-hidden group cursor-interaction">
                        <!-- Transition bg hover -->
                        <div class="absolute inset-0 bg-orange-600 translate-y-full group-hover:translate-y-0 transition-transform duration-700 ease-[cubic-bezier(0.77,0,0.175,1)] z-0"></div>
                        <div class="relative z-10 group-hover:text-white transition-colors duration-500">
                            <span class="font-sync text-xs tracking-[0.2em] uppercase mb-4 block font-bold">The Instinct</span>
                            <h2 class="text-5xl md:text-6xl font-black mb-6 leading-none">Deportes<br>Extremos</h2>
                            <p class="font-medium text-base md:text-lg opacity-80 max-w-md leading-relaxed">
                                Reflejos puros. Decisiones en fracciones de segundo. La gravedad y el vértigo como maestros. Ejecutar a máxima velocidad donde fallar no es una opción.
                            </p>
                        </div>
                    </div>

                    <!-- Zen / Meditación -->
                    <div class="w-full md:w-1/2 flex flex-col justify-center p-12 md:p-24 relative overflow-hidden group bg-slate-950 text-slate-200 cursor-interaction">
                        <div class="absolute inset-0 bg-slate-800 -translate-y-full group-hover:translate-y-0 transition-transform duration-700 ease-[cubic-bezier(0.77,0,0.175,1)] z-0"></div>
                        <div class="relative z-10">
                            <span class="font-sync text-xs tracking-[0.2em] uppercase mb-4 block font-bold text-slate-500">The Clarity</span>
                            <h2 class="text-5xl md:text-6xl font-black mb-6 leading-none">Mindful<br>Meditation</h2>
                            <p class="font-light text-base md:text-lg opacity-80 max-w-md leading-relaxed">
                                El silencio absoluto tras el caos. Visión estratégica en frío, paciencia monje para pulir el detalle invisible y disciplina mental inquebrantable en cada diseño.
                            </p>
                        </div>
                    </div>

                </div>
            </section>

            <!-- FOOTER / CTA -->
            <footer class="bg-black py-40 px-6 text-center relative z-50">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-10 tracking-tight">Tomar en serio cada proyecto.</h2>
                <p class="text-slate-500 font-sync text-xs tracking-[0.3em] mb-16 uppercase">No es producción en masa. Es ingeniería creativa.</p>
                
                <a href="mailto:info@jahzaelreyes.com.ar" class="inline-block relative group cursor-interaction">
                    <div class="absolute inset-0 bg-white/20 blur-2xl rounded-full group-hover:bg-white/40 transition-all duration-500"></div>
                    <span class="relative block px-12 py-5 bg-white text-black font-sync font-bold text-sm tracking-widest uppercase rounded-full hover:scale-105 transition-transform duration-300">
                        Iniciar Colaboración
                    </span>
                </a>
                
                <div class="mt-40 text-xs text-slate-700 font-mono flex flex-col justify-center gap-4">
                    <span>&copy; <?= date('Y') ?> Jahzael Reyes | Creative Technologist.</span>
                    <a href="/hub-core-kx92/" class="hover:text-slate-400 transition cursor-interaction">🔐 Private Workstation</a>
                </div>
            </footer>

        </div>
    </main>

    <script>
        // === CUSTOM CURSOR SYSTEM ===
        const cursorDot = document.querySelector('.cursor-dot');
        const cursorOutline = document.querySelector('.cursor-outline');
        
        // Solo aplicar el cursor magnético si no estamos en dispositivo táctil
        if(window.matchMedia("(pointer: fine)").matches) {
            window.addEventListener('mousemove', (e) => {
                gsap.to(cursorDot, { x: e.clientX, y: e.clientY, duration: 0.05 });
                gsap.to(cursorOutline, { x: e.clientX, y: e.clientY, duration: 0.3, ease: "power2.out" });
            });

            // Elementos magnéticos
            document.querySelectorAll('a, button, .cursor-interaction').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    gsap.to(cursorOutline, { scale: 1.8, borderColor: 'white', backgroundColor: 'rgba(255,255,255,0.05)', duration: 0.2 });
                });
                el.addEventListener('mouseleave', () => {
                    gsap.to(cursorOutline, { scale: 1, borderColor: 'rgba(255, 255, 255, 0.4)', backgroundColor: 'transparent', duration: 0.2 });
                });
            });
        }

        // === GSAP & SCROLLTRIGGER SETUP ===
        gsap.registerPlugin(ScrollTrigger);

        // --- HERO PARALLAX ---
        gsap.to(".hero-title", {
            scrollTrigger: {
                trigger: "#hero",
                start: "top top",
                end: "bottom top",
                scrub: 1
            },
            y: 150,
            opacity: 0
        });

        // --- ACT 1: THE ORIGIN ---
        gsap.from(".act1-text", {
            scrollTrigger: {
                trigger: "#act-1",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            y: 100, opacity: 0, duration: 1.2, ease: "power4.out"
        });
        
        gsap.from(".act1-visual", {
            scrollTrigger: {
                trigger: "#act-1",
                start: "top bottom",
                end: "bottom top",
                scrub: 1.5
            },
            y: -100, rotation: 5
        });

        // --- ACT 2: PHYSICAL MATTER ---
        gsap.from(".act2-text", {
            scrollTrigger: {
                trigger: "#act-2",
                start: "top 70%",
                toggleActions: "play none none reverse"
            },
            x: -100, opacity: 0, duration: 1.2, ease: "power4.out"
        });

        gsap.from(".act2-visual", {
            scrollTrigger: {
                trigger: "#act-2",
                start: "top bottom",
                end: "bottom top",
                scrub: 2
            },
            scale: 0.8, y: 150, rotation: -10
        });

        // --- ACT 3: AI SCENE PINNING ---
        // Congelamos la escena 3 un momento para enfocar el texto
        let tlAct3 = gsap.timeline({
            scrollTrigger: {
                trigger: "#act-3",
                start: "top top",
                end: "+=150%", // La pantalla se queda pegada durante 1.5x el alto de la ventana
                pin: true,
                scrub: 1
            }
        });
        
        tlAct3.from(".act3-title", { scale: 0.8, opacity: 0, filter: "blur(20px)", duration: 1 })
              .from(".act3-desc", { y: 50, opacity: 0, duration: 1 }, "-=0.5")
              .to(".act3-title", { scale: 1.2, opacity: 0.8, filter: "blur(10px)", duration: 2 })
              .to(".act3-desc", { opacity: 0, duration: 1 }, "-=1.5");

    </script>
</body>
</html>
