import React from 'react'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function About() {
  return (
    <>
      <Header />
      <Nav />
      <div className="container my-5 text-center">
        <h1 className="text-primary fw-bold display-4">About Our Company</h1>
        <p className="fs-5 text-muted mt-3">
          আমরা বিশ্বাস করি প্রযুক্তির সঠিক ব্যবহারে জীবন আরও সহজ হয়।
        </p>
      </div>
      <Footer />
    </>
  )
}
