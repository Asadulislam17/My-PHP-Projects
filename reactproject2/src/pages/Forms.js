import React, { useState } from 'react';
import axios from 'axios';
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function Forms() {
    
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        city: '',
        address: '',
        gender: '', 
        agree: false 
    });

    // const [submittedData, setSubmittedData] = useState(null);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData({
            ...formData, 
            [name]: type === 'checkbox' ? checked : value 
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        

        axios.post('http://localhost/My-PHP-Projects/reactproject2/api/user_insert.php', formData)
        .then(response => {
            if(response.data.status === 'success') {
                alert("🎉 " + response.data.message); 
                // setSubmittedData(formData);
                
        
                setFormData({
                    username: '', email: '', city: '', address: '', gender: '', agree: false
                });
            } else {
                alert("❌ সমস্যা হয়েছে: " + response.data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("⚠️ database a table koroni r xammp run koroni!");
        });
    };
    return (
        <div className="d-flex flex-column min-vh-100 bg-light">
            <Header />
            <Nav />
            
            <main className="flex-grow-1 py-5">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-12" style={{ maxWidth: '500px' }}>
                            
                            <div className="card shadow-lg border-0 p-4 p-md-5" style={{ borderRadius: '20px' }}>
                                <div className="text-center mb-4">
                                    <div className="display-4 mb-2">📝</div>
                                    <h1 className="fw-bold text-dark h2 mb-1">React Form</h1>
                                    <p className="text-muted small">Handling form inputs with React state</p>
                                </div>

                                <form onSubmit={handleSubmit}>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold text-secondary small">Your Name</label>
                                        <input 
                                            type="text" 
                                            name="username"
                                            value={formData.username}
                                            onChange={handleChange}
                                            className="form-control" 
                                            placeholder="John Doe"
                                            required
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold text-secondary small">Email Address</label>
                                        <input 
                                            type="email" 
                                            name="email"
                                            value={formData.email}
                                            onChange={handleChange}
                                            className="form-control" 
                                            placeholder="john@example.com"
                                            required
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold text-secondary small">Your City</label>
                                        <select 
                                            name="city"
                                            value={formData.city}
                                            onChange={handleChange}
                                            className="form-select"
                                            required
                                        >
                                            <option value="">Select City</option>
                                            <option value="Dhaka">Dhaka</option>
                                            <option value="Chittagong">Chittagong</option>
                                            <option value="Sylhet">Sylhet</option>
                                        </select>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold text-secondary small d-block">Gender</label>
                                        <div className="form-check form-check-inline">
                                            <input 
                                                type="radio" 
                                                name="gender"
                                                value="Male"
                                                checked={formData.gender === 'Male'} 
                                                onChange={handleChange}
                                                className="form-check-input" 
                                                id="male"
                                                required
                                            />
                                            <label className="form-check-label small" htmlFor="male">Male</label>
                                        </div>

                                        <div className="form-check form-check-inline">
                                            <input 
                                                type="radio" 
                                                name="gender"
                                                value="Female"
                                                checked={formData.gender === 'Female'} 
                                                onChange={handleChange}
                                                className="form-check-input" 
                                                id="female"
                                            />
                                            <label className="form-check-label small" htmlFor="female">Female</label>
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label fw-semibold text-secondary small">Address</label>
                                        <textarea 
                                            name="address"
                                            value={formData.address}
                                            onChange={handleChange}
                                            className="form-control"
                                            placeholder="Enter your address"
                                            required
                                        />
                                    </div>

                                    <div className="form-check mb-4">
                                        <input 
                                            type="checkbox" 
                                            name="agree"
                                            checked={formData.agree} 
                                            onChange={handleChange}
                                            className="form-check-input" 
                                            id="agreeCheck"
                                            required
                                        />
                                        <label className="form-check-label text-muted small" htmlFor="agreeCheck">
                                            I agree to all terms and conditions
                                        </label>
                                    </div>

                                    <button type="submit" className="btn btn-primary w-100 rounded-pill fw-semibold shadow-sm py-2">
                                        Submit Form 🚀
                                    </button>
                                </form>

                            </div>
                            
                        </div>
                    </div>
                </div>
            </main>

            <Footer />
        </div>
    );
}
