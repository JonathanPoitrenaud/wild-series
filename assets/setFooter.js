export default function setFooter(footerDivName, mainDivName) {
    const maxOffsetToScreenSize = 100;
    const mainContent = document.getElementById(mainDivName);
    const footer = document.getElementById(footerDivName);
    const screenSize = { width: window.innerWidth, height: window.innerHeight };
    // eslint-disable-next-line no-console
    console.log(screenSize.height);
    // eslint-disable-next-line no-console
    console.log(mainContent.clientHeight + footer.clientHeight + maxOffsetToScreenSize);
    if (
        screenSize.height
        < mainContent.clientHeight + footer.clientHeight + maxOffsetToScreenSize
    ) {
        footer.style.position = 'relative';
    }
}
