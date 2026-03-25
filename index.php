<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Road Mithra - Professional Roadside Assistance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <style>
        body { background: white; transition: background 0.5s; scroll-behavior: smooth; }
        :root {
            --primary: #1A237E;
            --secondary: #4527A0;
            --accent: #EC4899;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-dark: rgba(26, 35, 126, 0.05);
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(26, 35, 126, 0.95), rgba(69, 39, 160, 0.85)), 
                        url('assets/images/hero_banner.png');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            padding: 130px 10% 60px 10%;
            position: relative;
            overflow: hidden;
        }
        .hero-content { z-index: 2; max-width: 650px; }
        .hero-content h1 { font-size: 72px; font-weight: 900; line-height: 1.05; margin-bottom: 25px; letter-spacing: -2px; }
        .hero-content p { font-size: 20px; opacity: 0.9; margin-bottom: 45px; line-height: 1.6; font-weight: 400; }
        
        .nav-glass {
            background: rgba(26, 35, 126, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 15px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
        }

        .role-grid-horizontal {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin-top: 40px;
        }
        .admin-card {
            background: rgba(251, 191, 36, 0.15) !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
        }
        .admin-card:hover {
            background: linear-gradient(135deg, #F59E0B, #D97706) !important;
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.45) !important;
        }
        .role-web-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px 15px;
            border-radius: 24px;
            text-decoration: none;
            color: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .role-web-card:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .role-web-card i { font-size: 28px; transition: 0.3s; }
        .role-web-card h3 { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        
        .floating-shape {
            position: absolute;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            border-radius: 50%;
            z-index: 1;
            filter: blur(80px);
            opacity: 0.3;
        }

        .service-card {
            background: white;
            padding: 40px;
            border-radius: 32px;
            border: 1px solid #EEF2FF;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(26, 35, 126, 0.1);
            border-color: var(--primary);
        }
        .service-icon {
            width: 70px;
            height: 70px;
            background: #F5F7FF;
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 25px;
            transition: 0.3s;
        }
        .service-card:hover .service-icon {
            background: var(--primary);
            color: white;
            transform: rotate(-10deg);
        }

        .benefit-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: flex-start;
        }
        .benefit-icon {
            width: 48px;
            height: 48px;
            background: #FDF2F8;
            color: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
        }

        /* Background Motion Animations */
        @keyframes floatUp {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.4; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1; /* Below content but above background image */
            pointer-events: none;
        }
        .bg-animation i {
            position: absolute;
            color: #FFFFFF;
            opacity: 0;
            text-shadow: 0 0 15px rgba(255,255,255,0.6);
            animation: floatUp linear infinite;
        }
    </style>
</head>
<body>

    <nav class="nav-glass">
        <div style="display: flex; align-items: center; gap: 14px;">
            <img src="assets/images/logo.png" alt="Logo" style="width: 42px; border-radius: 10px;">
            <div>
                <div style="color: white; font-size: 20px; font-weight: 900; letter-spacing: -1px; line-height: 1;">ROAD MITHRA</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">Premium Assistance</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 40px; color: white; font-weight: 600; font-size: 14px;">
            <a href="#how-it-works" style="color: white; text-decoration: none; opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">How it works</a>
            <a href="auth/register.php" style="color: white; text-decoration: none; opacity: 0.8; transition: 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">Register</a>
            <a href="auth/login.php?role=customer" style="padding: 12px 30px; background: white; color: var(--primary); border-radius: 100px; text-decoration: none; font-weight: 800; box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Launch Portal</a>
        </div>
    </nav>

    <section class="hero-section">
        <div class="floating-shape" style="width: 500px; height: 500px; top: -200px; right: -100px;"></div>
        <div class="floating-shape" style="width: 400px; height: 400px; bottom: -100px; left: -100px; background: var(--secondary);"></div>

        <!-- Animated Background Icons -->
        <div class="bg-animation">
            <i class="fa-solid fa-car" style="left: 10%; animation-duration: 18s; animation-delay: 0s; font-size: 40px;"></i>
            <i class="fa-solid fa-motorcycle" style="left: 25%; animation-duration: 15s; animation-delay: 4s; font-size: 35px;"></i>
            <i class="fa-solid fa-wrench" style="left: 40%; animation-duration: 22s; animation-delay: 2s; font-size: 50px;"></i>
            <i class="fa-solid fa-car-side" style="left: 55%; animation-duration: 19s; animation-delay: 8s; font-size: 45px;"></i>
            <i class="fa-solid fa-screwdriver" style="left: 70%; animation-duration: 16s; animation-delay: 1s; font-size: 30px;"></i>
            <i class="fa-solid fa-gears" style="left: 85%; animation-duration: 24s; animation-delay: 6s; font-size: 55px;"></i>
            <i class="fa-solid fa-truck-pickup" style="left: 15%; animation-duration: 20s; animation-delay: 10s; font-size: 48px;"></i>
            <i class="fa-solid fa-car-burst" style="left: 80%; animation-duration: 17s; animation-delay: 12s; font-size: 38px;"></i>
            <i class="fa-solid fa-motorcycle" style="left: 50%; animation-duration: 25s; animation-delay: 15s; font-size: 42px;"></i>
            <i class="fa-solid fa-toolbox" style="left: 30%; animation-duration: 21s; animation-delay: 7s; font-size: 36px;"></i>
            
            <!-- Extra Cars and Bikes on Right Side -->
            <i class="fa-solid fa-motorcycle" style="left: 65%; animation-duration: 18s; animation-delay: 3s; font-size: 48px;"></i>
            <i class="fa-solid fa-car" style="left: 75%; animation-duration: 26s; animation-delay: 9s; font-size: 52px;"></i>
            <i class="fa-solid fa-car-side" style="left: 90%; animation-duration: 14s; animation-delay: 5s; font-size: 42px;"></i>
            <i class="fa-solid fa-motorcycle" style="left: 95%; animation-duration: 21s; animation-delay: 11s; font-size: 30px;"></i>
            <i class="fa-solid fa-car" style="left: 82%; animation-duration: 23s; animation-delay: 14s; font-size: 50px;"></i>
        </div>

        <div class="hero-content">
            <span style="display: inline-flex; align-items: center; gap: 10px; font-size: 13px; text-transform: uppercase; letter-spacing: 4px; font-weight: 800; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); color: white; padding: 10px 20px; border-radius: 100px; margin-bottom: 30px; border: 1px solid rgba(255,255,255,0.2);">
                <i class="fa-solid fa-star" style="color: #FBBF24;"></i> Trusted by 10,000+ Drivers
            </span>
            <h1>The Gold Standard in Roadside Care.</h1>
            <p>Don't let a breakdown ruin your day. Road Mithra connects you to professional mechanics and genuine parts in minutes, anywhere, anytime.</p>
            
            <div style="display: flex; gap: 20px; margin-bottom: 50px;">
                <a href="auth/register.php" style="padding: 18px 45px; background: var(--accent); color: white; border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 18px; box-shadow: 0 15px 30px rgba(236, 72, 153, 0.3); transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">Register Now</a>
                <a href="#services" style="padding: 18px 45px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 18px; backdrop-filter: blur(10px); transition: 0.3s; display: flex; align-items: center; gap: 10px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">Explore Services <i class="fa-solid fa-chevron-right" style="font-size: 14px;"></i></a>
            </div>
            
            <div class="role-grid-horizontal">
                <a href="auth/login.php?role=customer" class="role-web-card">
                    <i class="fa-solid fa-car-side"></i>
                    <h3>Customer</h3>
                </a>
                <a href="auth/login.php?role=mechanic_owner" class="role-web-card">
                    <i class="fa-solid fa-warehouse"></i>
                    <h3>Garage Owner</h3>
                </a>
                <a href="auth/login.php?role=parts_owner" class="role-web-card">
                    <i class="fa-solid fa-gears"></i>
                    <h3>Parts Store</h3>
                </a>
                <a href="auth/login.php?role=worker" class="role-web-card">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <h3>Service Team</h3>
                </a>
                <a href="auth/login.php?role=admin" class="role-web-card admin-card">
                    <i class="fa-solid fa-shield-check"></i>
                    <h3>Admin</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" style="padding: 120px 10%; background: white;">
        <div style="text-align: center; max-width: 800px; margin: 0 auto 80px auto;">
            <h4 style="color: var(--accent); font-weight: 800; letter-spacing: 5px; text-transform: uppercase; font-size: 14px; margin-bottom: 20px;">Comprehensive Solutions</h4>
            <h2 style="font-size: 48px; font-weight: 900; color: var(--primary); letter-spacing: -1.5px; line-height: 1.1;">One App, Every Roadside Service.</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-truck-pickup"></i></div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">24/7 Towing</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Professional towing service for all vehicle types. Flatbeds, wheel-lifts, and heavy-duty towing specialists available nearby.</p>
            </div>

            <div class="service-card">
                <div class="service-icon" style="background: #FFF7ED; color: #EA580C;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">On-Site Mechanics</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Get expert diagnostics and minor repairs right where you are. Our mobile mechanics carry tools for instant fixes.</p>
            </div>

            <div class="service-card">
                <div class="service-icon" style="background: #F0FDF4; color: #16A34A;"><i class="fa-solid fa-battery-three-quarters"></i></div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">Battery Support</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Jumpstarts and battery replacements available 24/7. We ensure your vehicle starts reliably everytime.</p>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section style="padding: 120px 10%; background: #F8FAFC;">
        <div style="display: flex; align-items: center; gap: 80px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 400px;">
                <h2 style="font-size: 48px; font-weight: 900; color: var(--primary); margin-bottom: 40px; letter-spacing: -1.5px;">Why Road Mithra is Your Best Travel Partner</h2>
                
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <h4 style="font-size: 20px; font-weight: 800; color: var(--primary); margin-bottom: 8px;">Lightning Fast Response</h4>
                        <p style="color: #64748B; line-height: 1.6;">Our smart algorithm finds the closest available worker to ensure you're never waiting long.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: #EFF6FF; color: #2563EB;"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div>
                        <h4 style="font-size: 20px; font-weight: 800; color: var(--primary); margin-bottom: 8px;">Real-Time Tracking</h4>
                        <p style="color: #64748B; line-height: 1.6;">Watch your assistance arrive in real-time. Know exactly who is coming and when they'll reach you.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon" style="background: #F0FDF4; color: #16A34A;"><i class="fa-solid fa-shield-heart"></i></div>
                    <div>
                        <h4 style="font-size: 20px; font-weight: 800; color: var(--primary); margin-bottom: 8px;">Verified Professionals</h4>
                        <p style="color: #64748B; line-height: 1.6;">Every workshop and mechanic on our platform is strictly vetted for quality and reliability.</p>
                    </div>
                </div>
            </div>
            
            <div style="flex: 0.8; position: relative;">
                <div style="background: var(--primary); border-radius: 40px; padding: 20px; transform: rotate(2deg);">
                    <img src="assets/images/logo.png" alt="App Preview" style="width: 100%; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
                </div>
                <!-- Floating badge -->
                <div style="position: absolute; top: -30px; left: -30px; background: white; padding: 25px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 50px; height: 50px; background: #FDE68A; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #D97706;">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 18px; color: var(--primary);">#1 Rated</div>
                        <div style="font-size: 13px; color: #64748B;">Roadside App 2024</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section id="how-it-works" style="padding: 120px 10%; background: white;">
        <div style="text-align: center; margin-bottom: 80px;">
            <h2 style="font-size: 42px; font-weight: 900; color: var(--primary); margin-bottom: 20px;">Simple. Fast. Seamless.</h2>
            <p style="color: #64748B; font-size: 18px; max-width: 600px; margin: 0 auto;">Follow these 3 easy steps to get back on the road in no time.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
            <!-- Step 1 -->
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; background: #EEF2FF; color: var(--primary); border-radius: 35px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px auto; position: relative;">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <div style="position: absolute; top: -10px; right: -10px; width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; border: 4px solid white;">1</div>
                </div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">Open & Select</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Launch the Road Mithra app and choose the service you need—towing, mechanic, or parts.</p>
            </div>

            <!-- Step 2 -->
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; background: #F5F3FF; color: #7C3AED; border-radius: 35px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px auto; position: relative;">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <div style="position: absolute; top: -10px; right: -10px; width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; border: 4px solid white;">2</div>
                </div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">Track Arrival</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Confirm your location and watch your assigned professional arrive in real-time on the map.</p>
            </div>

            <!-- Step 3 -->
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; background: #F0FDF4; color: #16A34A; border-radius: 35px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px auto; position: relative;">
                    <i class="fa-solid fa-road-circle-check"></i>
                    <div style="position: absolute; top: -10px; right: -10px; width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; border: 4px solid white;">3</div>
                </div>
                <h3 style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 15px;">Relax & Drive</h3>
                <p style="color: #64748B; line-height: 1.7; font-size: 16px;">Pay securely through the app once the job is done and safely continue your journey.</p>
            </div>
        </div>
    </section>

    <section style="padding: 100px 10%; background: var(--primary); color: white; position: relative; overflow: hidden;">
        <div class="floating-shape" style="width: 600px; height: 600px; top: -300px; right: -300px; opacity: 0.1;"></div>
        
        <div style="text-align: center; position: relative; z-index: 2;">
            <h2 style="font-size: 42px; font-weight: 900; margin-bottom: 60px;">Our Impact in Numbers</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
                <div>
                    <h1 style="font-size: 64px; font-weight: 900; color: var(--accent); margin-bottom: 10px;">150+</h1>
                    <p style="opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 12px;">Workshops</p>
                </div>
                <div>
                    <h1 style="font-size: 64px; font-weight: 900; color: var(--accent); margin-bottom: 10px;">2.5K</h1>
                    <p style="opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 12px;">Daily Assists</p>
                </div>
                <div>
                    <h1 style="font-size: 64px; font-weight: 900; color: var(--accent); margin-bottom: 10px;">12m</h1>
                    <p style="opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 12px;">Avg. Response</p>
                </div>
                <div>
                    <h1 style="font-size: 64px; font-weight: 900; color: var(--accent); margin-bottom: 10px;">99%</h1>
                    <p style="opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 12px;">Success Rate</p>
                </div>
            </div>
        </div>
    </section>

    <section style="padding: 120px 10%; text-align: center; background: white;">
        <div style="background: linear-gradient(135deg, #4527A0, #1A237E); padding: 80px; border-radius: 60px; color: white; position: relative; overflow: hidden;">
            <div style="position: relative; z-index: 2;">
                <h2 style="font-size: 48px; font-weight: 900; margin-bottom: 25px;">Ready to Get Started?</h2>
                <p style="font-size: 20px; opacity: 0.8; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">Join the thousands of drivers who trust Road Mithra for their safety on the road.</p>
                <div style="display: flex; gap: 20px; justify-content: center;">
                    <a href="auth/login.php?role=customer" style="padding: 20px 50px; background: white; color: var(--primary); border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 18px; box-shadow: 0 15px 30px rgba(0,0,0,0.2);">Launch Platform</a>
                    <a href="auth/register.php?role=mechanic_owner" style="padding: 20px 50px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 100px; text-decoration: none; font-weight: 800; font-size: 18px; backdrop-filter: blur(10px);">Partner with Us</a>
                </div>
            </div>
            <div class="floating-shape" style="width: 400px; height: 400px; bottom: -200px; right: -100px; background: var(--accent); opacity: 0.2;"></div>
        </div>
    </section>

    <footer style="padding: 60px 10%; background: #1A237E; color: white; text-align: center; border-top: 5px solid #EC4899;">
        <div style="font-weight: 800; font-size: 24px; margin-bottom: 10px;">ROAD MITHRA</div>
        <p style="opacity: 0.7; font-size: 14px; margin-bottom: 30px;">Your trusted partner for 24/7 roadside assistance across the country.</p>
        <div style="font-size: 13px; opacity: 0.5;">&copy; <?php echo date('Y'); ?> Road Mithra. All rights reserved.</div>
    </footer>

</body>
</html>
