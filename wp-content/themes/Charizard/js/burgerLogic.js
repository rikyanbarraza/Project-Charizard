window.addEventListener('load', (event) => {
    
    let burger = document.getElementById("burger-container")
    let mobileMenu = document.getElementById("mobileMenu")

    burger.addEventListener("click", showMobileMenu)


})




function showMobileMenu() {
    
     if(mobileMenu.style.display == "none") {
    mobileMenu.style.display = "block"
    } else {
    mobileMenu.style.display = "none"
    } 
    
    
}
