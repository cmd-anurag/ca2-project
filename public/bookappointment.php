<?php
session_start();

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include __DIR__ . "/../backend/includes/is_loggedin.php";

if ($_SESSION['user']['role'] != 'patient') {
    http_response_code(403);
    echo "Unauthorised";
    exit();
}

function e($output)
{
    return htmlspecialchars($output);
}

$userEmail = $_SESSION['user']['email'];
$userName = $_SESSION['user']['name'];

$nameArray = explode(" ", $userName);

if (isset($nameArray[0])) {
    $firstname = $nameArray[0];
}

if (isset($nameArray[1])) {
    $lastname = $nameArray[1];
}
if(!isset($lastname)) {
    $lastname = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | SwiftHealth</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chicle&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        .animate-progress {
            animation: progress 2s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navigation Starts-->
    <!-- Top Bar -->
    <div id="top" class="bg-blue-400 text-base text-white py-3 flex lg:items-center lg:justify-between w-full px-4">
        <div class="text-sm lg:text-lg text-left w-2/3">Working Hour: 08:00 AM to 09:00 PM | Email: info@swifthealth.com</div>
        <div class="flex items-center gap-2">
            <i class="fa-brands fa-facebook"></i>
            <span class="text-sm"> | Contact: +1 234 567 890</span>
        </div>
    </div>

    <!-- Navigation Bar Starts -->
    <nav id="navbar" class="flex items-center justify-between px-10 lg:px-16 py-6 w-full bg-white shadow-sm">
        <div id="logo" class="lg:text-4xl text-3xl font-bold text-black cursor-pointer font-[Chicle]">
            <a href="home.html">SwiftHealth</a>
        </div>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex lg:flex text-black md:text-[17px] lg:text-[18px] lg:gap-x-7 md:gap-1">
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="home.html">Home</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="dashboard.php">My Dashboard</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="aboutus.html">About Us</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="services.html">Services</a></div>
            <div class="hover:text-blue-500 duration-200 cursor-pointer p-1"><a href="ContactUs.html">Contact Us</a></div>
        </div>

        <div class="hidden md:flex items-center px-4 py-2 bg-gray-50 rounded-full">
            <i class="fa-solid fa-user text-blue-500"></i>
            <span class="ml-3 text-lg">
                <?php echo e($userName); ?>
            </span>
        </div>

        <!-- Mobile Menu Button -->
        <button id="menu-btn" class="md:hidden text-3xl">&#9776;</button>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu"
        class="fixed inset-0 bg-white text-black flex flex-col items-center justify-center text-xl space-y-6 transform -translate-y-full transition-transform duration-500 ease-in-out z-50">
        <button id="close-btn" class="absolute top-5 right-6 text-3xl">&#10006;</button>
        <a href="home.html" class="hover:text-blue-500">Home</a>
        <a href="dashboard.php" class="hover:text-blue-500">My Dashboard</a>
        <a href="aboutus.html" class="hover:text-blue-500">About Us</a>
        <a href="services.html" class="hover:text-blue-500">Services</a>
        <a href="ContactUs.html" class="hover:text-blue-500">Contact Us</a>
        <div class="bg-blue-500 text-white hover:bg-blue-950 cursor-pointer px-4 py-3 text-sm font-bold rounded-full">
            <a href="bookappointment.php">Book Appointment</a>
        </div>
    </div>

    <!-- Navigation Ends-->

    <!-- Banner Starts -->
    <div id="banner" class="bg-indigo-100 rounded-[50px] h-96 mx-5 my-5 bg-cover bg-center text-center p-6 sm:p-10 flex flex-col items-center justify-center shadow-md">
        <div class="lg:text-6xl text-4xl font-semibold font-[Sour_Gummy] text-gray-800 mb-8">Make An Appointment</div>
        <span class="bg-blue-500 text-white px-5 py-3 text-md font-bold rounded-full shadow-md transition-all hover:bg-blue-600">
            <a class="cursor-pointer hover:underline" href="./home.html">HOME</a> / APPOINTMENT
        </span>
    </div>
    <!-- Banner Ends -->

    <!-- Book Appointment-->
    <div class="bg-white rounded-[50px] mx-5 p-6 sm:p-10 lg:flex items-start shadow-lg">
        <div class="lg:w-1/2 md:w-1/2 space-y-4">
            <div class="flex gap-4">
                <input value="<?php echo e($firstname); ?>" class="bg-indigo-100 text-md rounded-[10px] p-3 w-1/2 border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="First Name" type="text" id="firstname">
                <input value="<?php echo e($lastname); ?>" class="bg-indigo-100 text-md rounded-[10px] p-3 w-1/2 border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Last Name" type="text" id="lastname">
            </div>
            <input disabled value="<?php echo e($userEmail); ?>" class="bg-indigo-100 text-md rounded-[10px] p-3 w-full border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Email Address" type="email">
            <input class="bg-indigo-100 text-md rounded-[10px] p-3 w-full border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Phone Number" type="text">
            <div class="flex gap-4">
                <input class="bg-indigo-100 text-md rounded-[10px] p-3 w-1/2 border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Choose a Date" type="date" min="<?php echo date('Y-m-d'); ?>" id="appdate">
                <select class="bg-indigo-100 text-md rounded-[10px] p-3 w-1/2 border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" id="specializationInput">
                    <option selected disabled>Select a specialization</option>
                    <option>Allergy & Immunology</option>
                    <option>Anesthesiology</option>
                    <option>Bariatrics</option>
                    <option>Cardiology</option>
                    <option>Chronic Care</option>
                    <option>Critical Care Medicine</option>
                    <option>Dentistry</option>
                    <option>Dermatology</option>
                    <option>Emergency Medicine</option>
                    <option>Endocrinology</option>
                    <option>Eyecare</option>
                    <option>Gastroenterology</option>
                    <option>General Surgery</option>
                    <option>Genetics</option>
                    <option>Geriatrics</option>
                    <option>Gynecology</option>
                    <option>Hematology</option>
                    <option>Hepatology</option>
                    <option>Infectious Disease</option>
                    <option>Internal Medicine</option>
                    <option>Neonatology</option>
                    <option>Nephrology</option>
                    <option>Neurology</option>
                    <option>Neurosurgery</option>
                    <option>Obstetrics</option>
                    <option>Oncology</option>
                    <option>Ophthalmology</option>
                    <option>Orthopedics</option>
                    <option>Otolaryngology (ENT)</option>
                    <option>Pathology</option>
                    <option>Pharmacy</option>
                    <option>Pediatrics</option>
                    <option>Physical Medicine & Rehabilitation</option>
                    <option>Plastic Surgery</option>
                    <option>Podiatry</option>
                    <option>Primary Care</option>
                    <option>Psychiatry</option>
                    <option>Pulmonology</option>
                    <option>Radiology</option>
                    <option>Rheumatology</option>
                    <option>Sports Medicine</option>
                    <option>Thoracic Surgery</option>
                    <option>Transplant Surgery</option>
                    <option>Urology</option>
                    <option>Vaccine</option>
                    <option>Vascular Surgery</option>
                    <option>Veterinary Medicine</option>
                </select>
            </div>
            <textarea id="remarksInput" class="bg-indigo-100 text-md rounded-[10px] p-3 w-full border border-indigo-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all h-32" placeholder="Enter remarks or additional details here"></textarea>
            <button id="book-button" class="bg-blue-500 text-white hover:bg-blue-600 py-3 px-6 rounded-full font-bold cursor-pointer transition-all duration-200 shadow-md hover:shadow-lg">Book Appointment</button>
        </div>

        <div class="lg:w-1/2 md:w-1/2 lg:mt-0 mt-10 ms-3 lg:text-left px-6">
            <h2 class="text-4xl font-bold text-black">Make an appointment</h2>
            <p class="text-lg text-gray-500 mt-2">Schedule your healthcare service with ease. Choose a date and specialist that works best for you.</p>

            <div class="flex items-center lg:justify-start gap-4 mt-6">
                <div class="bg-blue-100 p-3 rounded-full">
                    <div class="text-2xl text-blue-500"><i class="fa-solid fa-phone"></i></div>
                </div>
                <div>
                    <p class="text-lg font-bold">Customer Services</p>
                    <p class="text-gray-500">+1 (555) 123-4567</p>
                </div>
            </div>

            <div class="flex items-center lg:justify-start gap-4 mt-6">
                <div class="bg-blue-100 p-3 rounded-full">
                    <div class="text-2xl text-blue-500"><i class="fa-solid fa-clock"></i></div>
                </div>
                <div>
                    <p class="text-lg font-bold">Opening Hours</p>
                    <p class="text-gray-500">Mon - Sat (09:00 - 21:00)<br>Sunday (Closed)</p>
                </div>
            </div>
            
            <div class="flex items-center lg:justify-start gap-4 mt-6">
                <div class="bg-blue-100 p-3 rounded-full">
                    <div class="text-2xl text-blue-500"><i class="fa-solid fa-location-dot"></i></div>
                </div>
                <div>
                    <p class="text-lg font-bold">Our Location</p>
                    <p class="text-gray-500">123 Health Avenue, Medical District<br>New York, NY 10001</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Appointment Ends-->

    <!-- How We Work -->
    <div id="choose-us" class="bg-purple-50 rounded-[50px] mx-5 mt-10 lg:p-20 p-5">
        <div>
            <div class="text-m text-blue-700 text-center font-medium">How We Work</div>
            <div class="text-4xl lg:text-5xl text-black font-bold text-center lg:mx-[15%] py-5">We work to achieve better health outcomes</div>
            <div class="text-lg text-gray-500 text-center lg:mx-[20%]">We are committed to improving health outcomes through personalized care, innovative treatments, and a focus on prevention.</div>
        </div>
        <div id="content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-all text-center">
                <img class="rounded-full mx-auto w-24 h-24 object-cover" src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80" alt="Create Account">
                <div class="mt-4">
                    <span class="inline-block rounded-full bg-blue-500 text-white text-center w-8 h-8 leading-8">1</span>
                    <h3 class="text-xl font-bold mt-3">Create Account</h3>
                    <p class="text-gray-600 mt-2">Join our community by creating an account today.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-all text-center">
                <img class="rounded-full mx-auto w-24 h-24 object-cover" src="https://images.unsplash.com/photo-1624727828489-a1e03b79bba8?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80" alt="Book Appointment">
                <div class="mt-4">
                    <span class="inline-block rounded-full bg-blue-500 text-white text-center w-8 h-8 leading-8">2</span>
                    <h3 class="text-xl font-bold mt-3">Book Appointment</h3>
                    <p class="text-gray-600 mt-2">Effortlessly book an appointment according to your needs.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-all text-center">
                <img class="rounded-full mx-auto w-24 h-24 object-cover" src="https://images.unsplash.com/photo-1584982751601-97dcc096659c?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80" alt="Schedule Appointment">
                <div class="mt-4">
                    <span class="inline-block rounded-full bg-blue-500 text-white text-center w-8 h-8 leading-8">3</span>
                    <h3 class="text-xl font-bold mt-3">Schedule Appointment</h3>
                    <p class="text-gray-600 mt-2">Our scheduling algorithm will assign a doctor to you.</p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-all text-center">
                <img class="rounded-full mx-auto w-24 h-24 object-cover" src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80" alt="Start Consultation">
                <div class="mt-4">
                    <span class="inline-block rounded-full bg-blue-500 text-white text-center w-8 h-8 leading-8">4</span>
                    <h3 class="text-xl font-bold mt-3">Start Consultation</h3>
                    <p class="text-gray-600 mt-2">Consult the doctor after approval.</p>
                </div>
            </div>
        </div>
    </div>
    <!--How We Work Ends-->

    <!-- FOOTER -->
    <footer class="bg-indigo-950 text-white rounded-[50px] mx-3 my-5 lg:p-10 p-5">
            <div class="container mx-auto grid md:grid-cols-3 gap-10">
                <!-- Logo & Description -->
                <div>
                    <div id="logo" class="lg:text-4xl text-3xl font-bold text-indigo-50 font-[Chicle]">SwiftHealth</div>
                    <p class="text-gray-400 mt-3">We offer a wide range of healthcare services to meet your needs.</p>
                </div>
                
                <!-- Links Section -->
                <div class="grid grid-cols-2 gap-10">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Healthcare</h3>
                        <ul class="text-gray-400 space-y-2">
                            <li><a href="#" class="hover:underline">Doctors</a></li>
                            <li><a href="#" class="hover:underline">Diagnostics</a></li>
                            <li><a href="#" class="hover:underline">Caregiver</a></li>
                            <li><a href="#" class="hover:underline">Hospitality</a></li>
                            <li><a href="#" class="hover:underline">Emergency</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Quick Links</h3>
                        <ul class="text-gray-400 space-y-2">
                            <li><a href="home.html" class="hover:underline">Home</a></li>
                            <li><a href="aboutus.html" class="hover:underline">About Us</a></li>
                            <li><a href="#" class="hover:underline">FAQs</a></li>
                            <li><a href="#" class="hover:underline">Blog</a></li>
                            <li><a href="team.html" class="hover:underline">Our Team</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Contact Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-3">Contact Us</h3>
                    <ul class="text-gray-400 space-y-3">
                        <li class="flex items-center space-x-3">
                            <i class="fa-solid fa-envelope text-indigo-500"></i>
                            <span>contact@swifthealth.com</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fa-solid fa-phone text-indigo-500"></i>
                            <span>+1 234 567 890</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fa-solid fa-map-marker-alt text-indigo-500"></i>
                            <span>123 Main Street, City, Country</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-gray-700 my-6">
            
            <!-- Footer -->
            <div class="flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
                <div class="flex space-x-5 mt-3 md:mt-0">
                    <a href="#" class="bg-blue-700 py-3 px-4 rounded-full">
                        <i class="fa-brands fa-dribbble text-white"></i>
                    </a>
                    <a href="#" class="bg-blue-700 py-3 px-4 rounded-full">
                        <i class="fa-brands fa-facebook-f text-white"></i>
                    </a>
                    <a href="#" class="bg-blue-700 py-3 px-4 rounded-full">
                        <i class="fa-brands fa-instagram text-white"></i>
                    </a>
                </div>
                <div class="flex space-x-3 mt-3 md:mt-0">
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <span>•</span>
                    <a href="#" class="hover:underline">Terms & Conditions</a>
                </div>
            </div>
    </footer>
    <!-- FOOTER ENDS-->

    <!-- Modal Backdrop (hidden by default) -->
    <div id="modal-backdrop" class="fixed inset-0 backdrop-blur-sm flex items-center justify-center z-50 hidden transition-opacity duration-400 opacity-0">
        <!-- Modal Content -->
        <div id="modal-container" class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-transform duration-400 scale-95">
            <!-- Modal Header -->
            <div class="p-5 border-b">
                <div class="flex justify-between items-center">
                    <h3 id="modal-title" class="text-lg font-medium text-gray-900">Appointment Status</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Modal Body -->
            <div class="p-5">
                <div class="flex items-center">
                    <!-- Success Icon (hidden by default) -->
                    <div id="success-icon" class="hidden mr-3 flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <!-- Error Icon (hidden by default) -->
                    <div id="error-icon" class="hidden mr-3 flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p id="modal-message" class="text-sm text-gray-700"></p>
                    </div>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                <button id="modal-close-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Healthcare Icon Loader -->
    <div id="loader-element" class="loader fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-white/30 flex-col gap-5 hidden">
        <div class="flex flex-col items-center gap-5 p-8 bg-white rounded-2xl shadow-xl">
            <!-- Logo -->
            <div class="text-3xl font-bold text-blue-600 font-[Chicle]">SwiftHealth</div>
            
            <!-- Elegant loading animation -->
            <div class="relative w-24 h-24 flex items-center justify-center">
                <!-- Three dots with different animations -->
                <div class="flex space-x-4">
                    <div class="w-4 h-4 rounded-full bg-blue-600 animate-pulse"></div>
                    <div class="w-4 h-4 rounded-full bg-blue-500 animate-bounce" style="animation-delay: 0.3s"></div>
                    <div class="w-4 h-4 rounded-full bg-blue-400 animate-pulse" style="animation-delay: 0.6s"></div>
                </div>
            </div>
            
            <!-- Progress bar with animation -->
            <div class="w-48 bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-1.5 rounded-full animate-progress"></div>
            </div>
            
            <p class="text-gray-600 font-medium text-sm">Finding the best doctors for you...</p>
        </div>
    </div>

    <script src="./js/bookapp.js"></script>
    <script src="./js/script.js"></script>
</body>
</html>