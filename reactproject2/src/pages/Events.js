import React, { useState } from 'react'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function Events() {

    const [isOpen, setIsOpen] = useState(false);
    const [popupMessage, setPopupMessage] = useState("");

    const shoot = () => {
        setPopupMessage("🎉 Great Shot! You nailed it!");
        setIsOpen(true);
    }
    
    const shoot1 = (a) => {
        setPopupMessage(a);
        setIsOpen(true);
    }
    
    const Today = "Friday";
    
    return (
        <div className="d-flex flex-column min-vh-100 bg-light position-relative">
            <Header />
            <Nav />
     
            <main className="flex-grow-1 py-5">
                <div className="container">
                    
                    <div className="row g-4 justify-content-center mb-5">
                        <div className="col-12 col-md-6 d-flex justify-content-center" style={{ maxWidth: '420px' }}>
                            <div className="card shadow-lg border-0 text-center p-4 p-md-5 w-100" style={{ borderRadius: '15px' }}>
                                <div className="display-4 mb-3">🎯</div>
                                <h1 className="fw-bold text-dark h2 mb-2">React Click Event 1</h1>
                                <p className="text-muted small mb-4">
                                    নিচের বাটনে ক্লিক করে React-এর ইভেন্ট হ্যান্ডলার পরীক্ষা করুন।
                                </p>
                                <div className="d-grid col-10 mx-auto">
                                    <button onClick={shoot} className="btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm">
                                        🚀 Take the shot!
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="col-12 col-md-6 d-flex justify-content-center" style={{ maxWidth: '420px' }}>
                            <div className="card shadow-lg border-0 text-center p-4 p-md-5 w-100" style={{ borderRadius: '15px' }}>
                                <div className="display-4 mb-3">🎯</div>
                                <h1 className="fw-bold text-dark h2 mb-2">React Click Event 2</h1>
                                <p className="text-muted small mb-4">
                                    নিচের বাটনে ক্লিক করে React-এর ইভেন্ট হ্যান্ডলার পরীক্ষা করুন।
                                </p>
                                <div className="d-grid col-10 mx-auto">
                                    <button onClick={() => shoot1("Passing Arguments")} className="btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm">
                                        🚀 Take the shot!
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="row justify-content-center">
                        <div className="col-12" style={{ maxWidth: '860px' }}>
                            <div className="card shadow border-0 p-4" style={{ borderRadius: '15px' }}>
                                <h2 className="h4 fw-bold text-dark mb-4">⚙️ React Conditions</h2>
                                
                                <div className="row g-3">
                                    <div className="col-12 col-sm-6">
                                        <div className="p-3 border rounded-3 bg-white">
                                            <span className="text-muted small d-block mb-1">Ternary (?:) আউটপুট</span>
                                            <strong className="text-primary h5">
                                                {Today === "Friday" ? "Office Close" : "Office Open"}
                                            </strong>
                                        </div>
                                    </div>

                                    <div className="col-12 col-sm-6">
                                        <div className="p-3 border rounded-3 bg-white">
                                            <span className="text-muted small d-block mb-1">Logical And (&&) আউটপুট</span>
                                            <strong className="text-danger h5">
                                                {Today === "Friday" && "Friday"}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            {isOpen && (
                <div className="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                     style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }}>
                    <div className="card p-4 text-center shadow-lg border-0 animate__animated animate__zoomIn" 
                         style={{ maxWidth: '360px', width: '90%', borderRadius: '20px' }}>
                        <div className="display-3 mb-2">✨</div>
                        <h3 className="fw-bold text-dark h4 mb-3">সফল হয়েছে!</h3>
                        <p className="text-secondary mb-4">{popupMessage}</p>
                        <button onClick={() => setIsOpen(false)} className="btn btn-dark rounded-pill px-4 fw-semibold shadow-sm">
                            বন্ধ করুন
                        </button>
                    </div>
                </div>
            )}

            <Footer />
        </div>
    )
}
