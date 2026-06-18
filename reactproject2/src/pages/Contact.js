import React from 'react'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function Contact() {
  return (
    <>
      <Header />
      <Nav />
      <div className="container my-5 text-center">
        <h2 className="fw-bold text-primary">Get In Touch</h2>
        <p className="text-muted mt-2">আপনার যেকোনো জিজ্ঞাসা বা মতামতের জন্য আমাদের সাথে যোগাযোগ করুন।</p>
      </div>
      <Footer />
    </>
  )
}
