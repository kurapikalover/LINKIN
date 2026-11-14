document.addEventListener("click", (e) => {
  
    // Expand/hide details which also works for new cards
    const expandBtn = e.target.closest(".expand-details-btn");
    if(expandBtn){
    const extra = expandBtn.closest(".interview-section").querySelector(".interview-extra");
    const opening = extra.hidden;
    extra.hidden = !opening;
    expandBtn.textContent = opening ? "Hide Details" : "Expand Details";
    expandBtn.setAttribute("aria-expanded", opening);
    return;
  }

  //Accept for Interview -> add to the interview list
  const acceptBtn = e.target.closest(".acceptinterview-btn");
  if(acceptBtn){
    const card = acceptBtn.closest(".info-application");
    const copy = document.getElementById("interviewTemplate").content.cloneNode(true);

    copy.querySelector(".i-name").textContent = card.querySelector(".inline-row h2").textContent.trim();
    copy.querySelector(".i-id").textContent = card.querySelector(".p1").textContent;
    copy.querySelector(".i-faculty").textContent = card.querySelector(".p2").textContent;

    //copy rank rows (1,2,3 choices)
    const ranks= copy.querySelector(".i-ranks");
    card.querySelectorAll(".rank-table tr").forEach(row => ranks.appendChild(row.cloneNode(true)));

    document.getElementById("interviewList").appendChild(copy);
    card.remove() //leaves the Applications list
    return;
  }

  //Reject so remove from Applications
  const rejectBtn = e.target.closest(".info-application .reject-btn");
  if (rejectBtn) rejectBtn.closest(".info-application").remove();
});